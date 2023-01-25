<?php

namespace ryun42680\guildshopping\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use ryun42680\guildshopping\GuildShopping;
use ryun42680\inventorymenuapi\InventoryMenuAPI;
use ryun42680\inventorymenuapi\InventoryType;
use skh6075\lib\itemparser\ItemParser;

final class EditShopCommand extends Command
{

    public function __construct()
    {
        parent::__construct('길드상점수정', '길드 상점을 수정합니다.');
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($this->testPermission($sender) and $sender instanceof Player) {
            $inv = InventoryMenuAPI::getInstance()->create($sender->getPosition(), '길드 상점 수정', InventoryType::INVENTORY_TYPE_DOUBLE_CHEST);
            $inv->setFirstContents(GuildShopping::getInstance()->getItems());
            $inv->setCloseHandler(function (Player $player) use ($inv): void {
                GuildShopping::getInstance()->setItems(array_map(function (Item $item) use ($inv): array {
                    return ItemParser::toArray($item);
                }, $inv->getContents(false)));
                $player->sendMessage(GuildShopping::$prefix . '길드 상점을 수정했습니다.');
            })->send($sender);
        }
    }
}