<?php

namespace Azuriom\AzLink\PocketMine;

use Azuriom\AzLink\PocketMine\Commands\AzLinkCommand;
use Azuriom\AzLink\PocketMine\Http\HttpClient;
use Azuriom\AzLink\PocketMine\Tasks\FetcherTask;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

class AzLinkPM extends PluginBase
{
    /**
     * @internal Should only be used in FetcherAsyncTask
     */
    public static AzLinkPM $instance;

    public HttpClient $httpClient;

    public function onLoad(): void
    {
        static::$instance = $this;

        require_once __DIR__.'/../vendor/autoload.php';
            
        $this->getServer()->getAsyncPool()->addWorkerStartHook(function(int $worker): void {
            $this->getServer()->getAsyncPool()->submitTaskToWorker(new class extends AsyncTask {
                public function onRun(): void
                {
                    require_once __DIR__.'/../vendor/autoload.php';
                }
            }, $worker);
        });

        $this->httpClient = new HttpClient($this);

        $this->getServer()->getCommandMap()->register('azlink', new AzLinkCommand($this));

        $this->getScheduler()->scheduleDelayedRepeatingTask(new FetcherTask($this), 60 * 20, (60 - date('s')) * 20);
    }

    public function getServerData(bool $full = true): array
    {
        $players = array_map(function (Player $player) {
            return [
                'name' => $player->getName(),
                'uuid' => $player->getXuid(),
            ];
        }, array_values($this->getServer()->getOnlinePlayers()));

        $data = [
            'platform' => [
                'type' => 'POCKETMINE',
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

        $worlds = $this->getServer()->getWorldManager()->getWorlds();

        $entities = array_reduce($worlds, function (int $sum, World $world) {
            return $sum + count($world->getEntities());
        }, 0);

        $chunks = array_reduce($worlds, function (int $sum, World $world) {
            return $sum + count($world->getLoadedChunks());
        }, 0);

        return array_merge($data, [
            'system' => [
                'cpu' => $this->getLoadAverage(),
                'ram' => memory_get_usage() / 1024 / 1024,
            ],
            'worlds' => [
                'tps' => $this->getServer()->getTicksPerSecond(),
                'chunks' => $chunks,
                'entities' => $entities,
            ],
        ]);
    }

    private function getLoadAverage()
    {
        // sys_getloadavg is not implemented on Windows platforms.
        $load = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : false;

        return is_float($load) ? $load : -1;
    }
}
