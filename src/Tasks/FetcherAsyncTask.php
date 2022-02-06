<?php

namespace Azuriom\AzLink\PocketMine\Tasks;

use Azuriom\AzLink\PocketMine\AzLinkPM;
use Azuriom\AzLink\PocketMine\Http\PendingPostRequest;
use Exception;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\scheduler\AsyncTask;

class FetcherAsyncTask extends AsyncTask
{
    protected PendingPostRequest $request;

    public function __construct(PendingPostRequest $request)
    {
        $this->request = $request;
    }

    public function onRun(): void
    {
        try {
            $response = $this->request->send();

            $this->setResult($response);
        } catch (Exception $e) {
            // Exceptions with a request object can't be serialized
            $this->setResult(new Exception($e->getMessage(), $e->getCode()));
        }
    }

    public function onCompletion(): void
    {
        $response = $this->getResult();

        if ($response instanceof Exception) {
            $this->getPlugin()->getLogger()->error('Unable to send data to the website: '.$response->getMessage());

            return;
        }

        if (! is_array($response) || empty($response['commands'] ?? [])) {
            return;
        }

        $server = $this->getPlugin()->getServer();
        $console = new ConsoleCommandSender($server, $server->getLanguage());
        $commands = $response['commands'];

        $this->getPlugin()->getLogger()->info('Dispatching commands to '.count($commands).' players.');

        foreach ($commands as $playerName => $playerCommands) {
            $player = $server->getPlayerExact($playerName);

            foreach ($playerCommands as $command) {
                $command = str_replace([
                    '{player}', '{uuid}',
                ], $player ? [
                    $player->getName(), $player->getXuid(),
                ] : [$playerName, '?'], $command);

                $this->getPlugin()->getLogger()->info("Dispatching command for player {$playerName}: {$command}");

                $server->dispatchCommand($console, $command);
            }
        }
    }

    protected function getPlugin(): AzLinkPM
    {
        return AzLinkPM::$instance;
    }

    public static function sendData(AzLinkPM $plugin, bool $full = true): void
    {
        $data = $plugin->getServerData($full);

        $asyncTask = new self($plugin->httpClient->preparePostRequest($data));

        $plugin->getServer()->getAsyncPool()->submitTask($asyncTask);
    }
}
