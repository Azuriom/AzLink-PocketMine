<?php

namespace Azuriom\AzLink\PocketMine\Tasks;

use Azuriom\AzLink\PocketMine\AzLinkPM;
use DateTime;
use DateTimeInterface;
use pocketmine\scheduler\Task;

class FetcherTask extends Task
{
    /** @var DateTimeInterface */
    protected $lastFullSent;

    /** @var DateTimeInterface */
    protected $lastSent;

    /** @var AzLinkPM */
    protected $plugin;

    public function __construct(AzLinkPM $plugin)
    {
        $this->plugin = $plugin;

        $this->lastSent = new DateTime('01/01/2020');
        $this->lastFullSent = new DateTime('01/01/2020');
    }

    public function onRun(int $currentTick): void
    {
        $siteKey = $this->plugin->getConfig()->get('siteKey');
        $siteUrl = $this->plugin->getConfig()->get('siteUrl');

        $now = new DateTime();

        if (empty($siteKey) || empty($siteUrl) || $this->lastSent->diff($now)->s > 15) {
            return;
        }

        $sendFullData = $now->format('i') % 15 === 0 && $this->lastFullSent->diff($now)->i > 1;

        FetcherAsyncTask::sendData($this->plugin);

        if ($sendFullData) {
            $this->lastFullSent = new DateTime();
        }
    }
}
