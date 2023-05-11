<?php

namespace Azuriom\AzLink\PocketMine\Commands;

use Azuriom\AzLink\PocketMine\AzLinkPM;
use Azuriom\AzLink\PocketMine\Tasks\FetcherAsyncTask;
use Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class AzLinkCommand extends Command implements PluginOwned
{
    private AzLinkPM $plugin;

    public function __construct(AzLinkPM $plugin)
    {
        parent::__construct('azlink', 'Manage the AzLink plugin.', '/azlink [status|setup|fetch|port]', ['azuriomlink']);
        $this->setPermission('azlink.admin');
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (empty($args)) {
            $this->sendUsage($sender);

            return;
        }

        if ($args[0] === 'setup') {
            if (count($args) < 3) {
                $sender->sendMessage(TextFormat::RED.'Usage: /azlink setup <url> <key>');

                return;
            }

            $this->setup($sender, $args[1], $args[2]);

            return;
        }

        if ($args[0] === 'status') {
            $this->showStatus($sender);

            return;
        }

        if ($args[0] === 'fetch') {
            FetcherAsyncTask::sendData($this->plugin);

            $sender->sendMessage(TextFormat::GREEN.'Data has been fetched successfully.');

            return;
        }

        $this->sendUsage($sender);
    }

    public function getOwningPlugin(): AzLinkPM
    {
        return $this->plugin;
    }

    private function sendUsage(CommandSender $sender): void
    {
        $version = $this->plugin->getDescription()->getVersion();

        $sender->sendMessage(TextFormat::BLUE."AzLink v{$version}. Website: https://azuriom.com");
        $sender->sendMessage(TextFormat::GRAY.'- /azlink setup <url> <key>');
        $sender->sendMessage(TextFormat::GRAY.'- /azlink status');
        $sender->sendMessage(TextFormat::GRAY.'- /azlink fetch');
    }

    private function setup(CommandSender $sender, string $url, string $key): void
    {
        $config = $this->plugin->getConfig();
        $config->set('siteKey', $key);
        $config->set('siteUrl', $url);

        if ($this->showStatus($sender)) {
            $config->save();
        }
    }

    private function showStatus(CommandSender $sender): bool
    {
        try {
            $this->plugin->httpClient->getStatus();

            $sender->sendMessage(TextFormat::GREEN.'Linked to the website successfully.');

            return true;
        } catch (Exception $e) {
            $sender->sendMessage('§cUnable to connect to the website: '.$e->getMessage());

            return false;
        }
    }
}
