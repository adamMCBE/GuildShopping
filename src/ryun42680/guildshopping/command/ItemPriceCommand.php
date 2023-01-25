<?php

namespace ryun42680\guildshopping\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use ryun42680\guildshopping\GuildShopping;

final class ItemPriceCommand extends Command
{

    public function __construct()
    {
        parent::__construct('길드아이템가격', '길드 상점 아이템 가격을 설정합니다.');
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($this->testPermission($sender) and $sender instanceof Player) {
            if (count($args) >= 2) {
                if (count(array_filter($args, 'is_numeric')) >= 2) {
                    $item = $sender->getInventory()->getItemInHand();
                    if (!$item->isNull()) {
                        GuildShopping::getInstance()->setPrice($item, $args [0], $args [1]);
                        $sender->sendMessage(GuildShopping::$prefix . '아이템 가격을 설정했습니다.');
                    } else {
                        $sender->sendMessage(GuildShopping::$prefix . '공기의 가격은 설정할 수 없습니다.');
                    }
                } else {
                    $sender->sendMessage(GuildShopping::$prefix . '가격을 숫자로 기입해주세요');
                }
            } else {
                $sender->sendMessage(GuildShopping::$prefix . '/상점아이템설정 [구매가] [판매가] : 아이템 가격을 설정합니다.');
            }
        }
    }
}