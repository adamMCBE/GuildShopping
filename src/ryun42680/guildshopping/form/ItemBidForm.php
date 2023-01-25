<?php

namespace ryun42680\guildshopping\form;

use pocketmine\form\Form;
use pocketmine\item\Item;
use pocketmine\player\Player;
use ryun42680\economyapi\EconomyAPI;
use ryun42680\guildapi\Guild;
use ryun42680\guildapi\GuildAPI;
use ryun42680\guildshopping\GuildShopping;
use function floor;

final class ItemBidForm implements Form
{

    private GuildShopping $shopping;
    private string $title;
    private int $buyprice, $sellprice;

    public function __construct(private Item $item)
    {
        $this->shopping = GuildShopping::getInstance();
        $this->title = $this->item->getName();
        $this->buyprice = $this->shopping->getBuyPrice($item);
        $this->sellprice = $this->shopping->getSellPrice($item);
    }

    public function jsonSerialize(): array
    {
        $prices = [];

        if ($this->buyprice >= 0) {
            $prices [] = '구매: ' . $this->buyprice . 'RP';
        }

        if ($this->sellprice >= 0) {
            $prices [] = '판매: ' . $this->sellprice . 'RP';
        }

        return [
            'type' => 'custom_form',
            'title' => '§l§0' . $this->title,
            'content' => [
                [
                    'type' => 'label',
                    'text' => implode(PHP_EOL, array_merge([
                        '',
                        $this->title . ' §r§f(을)를 구매 또는 판매하시겠습니까?',
                        ''
                    ], $prices))
                ],

                [
                    'type' => 'dropdown',
                    'text' => ' ',
                    'options' => [
                        '구매하기', '판매하기'
                    ]
                ],

                [
                    'type' => 'input',
                    'text' => PHP_EOL . '원하시는 수량을 적어주세요.'
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if (count($data) >= 3) {
            if (is_numeric($data [2])) {
                $guild = GuildAPI::getInstance()->myGuild($player);

                if ($guild instanceof Guild) {
                    $mymoney = $guild->getPointByPlayer($player);
                    $item = $this->item;
                    $count = abs($data [2]);

                    if ($data [1] === 0) {
                        if ($this->buyprice >= 0) {
                            $buyprice = $this->buyprice * floor($count);

                            if ($mymoney >= $buyprice) {
                                if (!$player->getInventory()->canAddItem($item->setCount(floor($count)))) {
                                    $player->sendMessage(GuildShopping::$prefix . '현재 소지품이 가득 찼습니다');
                                    return;
                                }
                                $player->getInventory()->addItem($item);
                                $player->sendMessage(GuildShopping::$prefix . $this->title . ' §r§7(을)를 §a' . floor($count) . '§7개 구매하셨습니다.');
                                $player->sendMessage(GuildShopping::$prefix . '소비 RP: ' . EconomyAPI::getInstance()->KoreanWonFormat($buyprice, 'RP') . ', 기존 소지 RP: ' . EconomyAPI::getInstance()->KoreanWonFormat($mymoney, 'RP') . ', 최종 소지 RP: ' . EconomyAPI::getInstance()->KoreanWonFormat($mymoney - $buyprice, 'RP'));
                                $guild->reducePoint($buyprice, $player);
                            } else {
                                $player->sendMessage(GuildShopping::$prefix . '소지중인 RP가 부족합니다.');
                            }
                        } else {
                            $player->sendMessage(GuildShopping::$prefix . '구매할 수 없는 상품입니다.');
                        }
                    } else {
                        if ($this->sellprice >= 0) {
                            if ($player->getInventory()->contains($item->setCount(floor($count))) >= $data [2]) {
                                $sellprice = $this->sellprice * floor($data [2]);
                                $player->getInventory()->removeItem($item);
                                $player->sendMessage(GuildShopping::$prefix . $item->getName() . ' §r§7(을)를 §a' . floor($count) . '§7개 판매하셨습니다.');
                                $player->sendMessage(GuildShopping::$prefix . '얻은 RP: ' . EconomyAPI::getInstance()->KoreanWonFormat($sellprice, 'RP') . ', 기존 소지 RP: ' . EconomyAPI::getInstance()->KoreanWonFormat($mymoney, 'RP') . ', 최종 소지 RP: ' . EconomyAPI::getInstance()->KoreanWonFormat($mymoney + $sellprice, 'RP'));
                                $guild->addPoint($sellprice, $player);
                            } else {
                                $player->sendMessage(GuildShopping::$prefix . '소지중인 아이템이 부족합니다.');
                            }
                        } else {
                            $player->sendMessage(GuildShopping::$prefix . '판매할 수 없는 상품입니다.');
                        }
                    }
                }
            } else {
                $player->sendMessage(GuildShopping::$prefix . '수량은 숫자로 기입해주세요.');
            }
        }
    }
}