<?php

namespace Azuriom\AzLink\PocketMine\Tasks;

use Azuriom\AzLink\PocketMine\AzLinkPM;
use Azuriom\AzLink\PocketMine\Http\PendingPostRequest;
use Exception;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class FetcherAsyncTask extends AsyncTask
{
    /** @var PendingPostRequest */
    protected $request;

    public function __construct(PendingPostRequest $request)
    {
        $this->request = $request;
    }

    public function onRun()
    {
        try {
            $response = $this->request->send();

            $this->setResult($response);
        } catch (Exception $e) {
            $this->setResult($e);
        }
    }

    public function onCompletion(Server $server)
    {
        $response = $this->getResult();

        if ($response instanceof Exception) {
            $this->getPlugin()->getLogger()->error('Unable to send data to the website: '.$response->getMessage());

            return;
        }

        if (! is_array($response) || empty($response['commands'] ?? [])) {
            return;
        }

        $commands = $response['commands'];

        $this->getPlugin()->getLogger()->info('Dispatching commands to '.count($commands).' players.');

        foreach ($commands as $playerName => $playerCommands) {
            $player = $this->getPlugin()->getServer()->getPlayer($playerName);

            foreach ($playerCommands as $command) {
                $command = str_replace([
                    '{player}', '{uuid}',
                ], $player ? [
                    $player->getName(), $player->getXuid(),
                ] : [$playerName, '?'], $command);

                $this->getPlugin()->getLogger()->info("Dispatching command for player {$playerName}: {$command}");

                $this->getPlugin()->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
            }
        }
    }

    protected function getPlugin()
    {
        return AzLinkPM::$instance;
    }

    public static function sendData(AzLinkPM $plugin, bool $full = true)
    {
        $data = $plugin->getServerData($full);

        $asyncTask = new self($plugin->httpClient->preparePostRequest($data));

        $plugin->getServer()->getAsyncPool()->submitTask($asyncTask);
    }
}
