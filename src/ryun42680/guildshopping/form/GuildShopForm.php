<?php

namespace ryun42680\guildshopping\form;

use pocketmine\form\Form;
use pocketmine\item\Item;
use pocketmine\player\Player;
use ryun42680\guildshopping\GuildShopping;

final class GuildShopForm implements Form
{

    private GuildShopping $shopping;

    public function __construct()
    {
        $this->shopping = GuildShopping::getInstance();
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => 'form',
            'title' => '§l§0길드 상점',
            'content' => ' ',
            'buttons' => array_map(function (Item $item): array {
                $subtitle = '';

                if (($buyprice = $this->shopping->getBuyPrice($item)) >= 0) {
                    $subtitle .= '§r§8구매: ' . $buyprice . 'RP';
                }

                if (($sellprice = $this->shopping->getSellPrice($item)) >= 0) {
                    if ($subtitle !== '') $subtitle .= ' / ';
                    $subtitle .= '판매: ' . $sellprice . 'RP';
                }

                return [
                    'text' => '§l§0' . $item->getName() . PHP_EOL . $subtitle
                ];
            }, $this->shopping->getItems())
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if (is_numeric($data)) {
            $player->sendForm(new ItemBidForm($this->shopping->getItems() [$data]));
        }
    }
}