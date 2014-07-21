Developer's guide
===
## I want to add a faction
Each faction is an object created on database load. If you want to add a faction, a new faction has to be created. As a third-party plugin developer, we recommend you to use the `Faction::newInstance()` function to create a faction that is automatically registered.

This is the signature of the function:

```php
public static function \pocketfactions\faction\Faction::newInstance(string $name, string $founder, Rank[] $ranks, int $defaultRankIndex, int $allyRankIndex, int $truceRankIndex, int $stdRankIndex, \pocketfaction\Main $main, Position|Position[] $homes [, string $motto = "" [, bool $whitelist = true [, int $id = \pocketfactions\faction\Faction::nextID($main) ] ] ] );
```

Now, this is the documentation for each parameter:
* `string $name`: This is the name of the faction. It doesn't have to be unique. However, its not being unique could lead to issues when the player uses commands that refer to the faction's name, like `/f rel`, `/f join`, etc. Any strings are accepted, as long as the server owner would accept.
* `string $founder`: This is the lowercase name of the faction founder. This foundership is constant unless it is passed to a successor due to his inactivity. If the faction is server-owned, put "console" for that. This parameter only serves as a function to give automatic foundership to that player.
* `Rank[] $ranks`: This is an array of `Rank` objects. They can be instantiated using `new Rank($id, $name, $permissions, $description` where `$permissions` is the sum of the permission node constants as in `Rank.php`, accessed by `Rank::P_***`.
* `int $defaultRankIndex`, `int $allyRankIndex`, `int $truceRankIndex` and `int $stdRankIndex`: This is the key of the rank in `$ranks` that is the rank a player has when he newly joins, is in an ally faction, is in a truce faction, and is in an enemy/neutral/no faction. For normal factions (factions created using `/f create` and recruit using `/f invite` or `/f join`), the default one is member.
* `\pocketfactions\Main $main`: This is an instance of the faction's main class. You can get it by `$server->getPluginManager()->getPlugin("PocketFactions")`, where `$server` is an instance of `\pocketmine\Server`, usually resolved by `$this->getServer()` or `\pocketmine\Server::getInstance()`.
* `Position|Position[] $homes`: This are the default homes of the faction. It is safe to just put `[]` (empty array) for that, since homes aren't always necessary.
* `string $motto`: This is the default motto of the faction. It can be left as blank.
* `bool $whitelist`: This is the boolean value of whether players have to be invited to join the faction. Default is `true`.
* `int $id`: This is the ID of the faction. Do not put a value for this parameter unless you know what you are doing.

After calling this function, your new faction object should be returned. This function would automatically register the faction to the faction list and the in-memory database for indexing.

## I want to get the object of an existing faction
Currently, there are several ways of getting a faction. There are four identifiers available:

* Search by faction ID (fastest way)
* Search by faction name
* Search by faction alike name (like how players are found with alike names)
* Search by a chunk claimed by a faction
* Search by faction's member (a player object must be provided)

You can get a faction object by using the `FactionList::getFaction(mixed)` method. For example:

```php
$pocketfactions = $this->getServer()->getPluginManager()->getPlugin("PocketFactions");
$list = $pocketfactions->getFList();
// get faction by exact name
$named = $list->getFaction("Named");
// get faction by similar name
$similarName = $list->getFaction("Similar");
// get the faction of the player
$playerFaction = $list->getFaction($this->getServer()->getPlayer("Player"));
if(!($playerFaction instanceof Faction)){
    // player doesn't belong to any factions
}
$chunkOwner = $list->getFaction(Chunk::fromObject($position));
```

## I want to add faction money
Faction is a subclass of xEcon entity. Refer to xEcon documentation for how to add money to xEcon entities.
