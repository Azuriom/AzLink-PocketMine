<?php

namespace Azuriom\AzLink\PocketMine;

use Azuriom\AzLink\PocketMine\Commands\AzLinkCommand;
use Azuriom\AzLink\PocketMine\Http\HttpClient;
use Azuriom\AzLink\PocketMine\Tasks\FetcherTask;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class AzLinkPM extends PluginBase
{
    /**
     * @var AzLinkPM
     * @internal Should only be use in FetcherAsyncTask
     */
    public static $instance;

    /**
     * The AzLink HTTP client instance.
     *
     * @var HttpClient
     */
    public $httpClient;

    public function onEnable(): void
    {
        static::$instance = $this;

        require_once __DIR__.'/../../../../vendor/autoload.php';

        $this->httpClient = new HttpClient($this);

        $this->getServer()->getCommandMap()->register($this->getName(), new AzLinkCommand($this));

        $this->getScheduler()->scheduleDelayedRepeatingTask(new FetcherTask($this), 60 * 20, (60 - date('s')) * 20);
    }

    /**
     * Get the server data.
     *
     * @param  bool  $full
     * @return array
     */
    public function getServerData(bool $full = true): array
    {
        $players = array_map(function (Player $player) {
            return [
                'name' => $player->getName(),
                'uuid' => $player->getXuid(),
            ];
        }, $this->getServer()->getOnlinePlayers());

        $data = [
            'platform' => [
                'type' => 'POCKET_MINE',
                'name' => $this->getServer()->getName(),
                'version' => $this->getServer()->getVersion(),
            ],
            'version' => $this->getDescription()->getVersion(),
            'players' => $players,
            'maxPlayers' => $this->getServer()->getMaxPlayers(),
            'full' => $full,
        ];

        if (! $full) {
            return $data;
        }

        $entities = array_reduce($this->getServer()->getLevels(), function (int $sum, Level $level) {
            return $sum + count($level->getEntities());
        }, 0);

        $chunks = array_reduce($this->getServer()->getLevels(), function (int $sum, Level $level) {
            return $sum + count($level->getChunks());
        }, 0);

        return array_merge($data, [
            'system' => [
                'cpu' => sys_getloadavg()[0],
                'ram' => memory_get_usage(),
            ],
            'worlds' => [
                'tps' => $this->getServer()->getTicksPerSecond(),
                'chunks' => $chunks,
                'entities' => $entities,
            ],
        ]);
    }
}
