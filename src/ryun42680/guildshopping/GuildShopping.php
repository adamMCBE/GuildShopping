<?php

namespace ryun42680\guildshopping;

use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use ryun42680\guildapi\GuildAPI;
use ryun42680\guildshopping\command\EditShopCommand;
use ryun42680\guildshopping\command\ItemPriceCommand;
use ryun42680\inventorymenuapi\InventoryMenuAPI;
use skh6075\lib\itemparser\ItemParser;

final class GuildShopping extends PluginBase
{

    public static string $prefix = '§l§b[길드상점]§r§7 ';

    use SingletonTrait;

    protected Config $config;
    protected array $database;

    protected function onEnable(): void
    {
        if (class_exists(GuildAPI::class) and class_exists(InventoryMenuAPI::class)) {
            $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML, [[], []]);
            $this->database = $this->config->getAll();
            $this->getServer()->getCommandMap()->registerAll(strtolower($this->getName()), [
                new EditShopCommand(), new ItemPriceCommand()
            ]);
        } else {
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    protected function onDisable(): void
    {
        $this->config->setAll($this->database);
        $this->config->save();
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    public function getBuyPrice(Item $item): int
    {
        return $this->getPrice($item) [0];
    }

    public function getSellPrice(Item $item): int
    {
        return $this->getPrice($item) [1];
    }

    public function getPrice(Item $item): array
    {
        $item->setCount(1);
        return $this->database [0] [$item->__toString()] ?? [-1, -1];
    }

    public function setPrice(Item $item, int $buyPrice, int $sellPrice): void
    {
        $item->setCount(1);
        $this->database [0] [$item->__toString()] = [$buyPrice, $sellPrice];
    }

    public function getItems(): array
    {
        return array_map(function (array $data): Item {
            return ItemParser::fromArray($data);
        }, $this->database [1]);
    }

    public function setItems(array $items): void
    {
        $this->database [1] = $items;
    }
}