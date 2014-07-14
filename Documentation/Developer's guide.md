Developer's guide
===
## I want to add a faction
Each faction is an object created on database load. If you want to add a faction, a new faction has to be created. As a third-party plugin developer, we recommend you to use the `Faction::newInstance()` function to create a faction that is automatically registered.

This is the signature of the function:

```php
public static function \pocketfactions\faction\Faction::newInstance(string $name, string $founder, Rank[] $ranks, int $defaultRankIndex, \pocketfaction\Main $main, Position|Position[] $homes [, string $motto = "" [, bool $whitelist = true [, int $id = \pocketfactions\faction\Faction::nextID($main) ] ] ] );
```

Now, this is the documentation for each parameter:
* `string $name` is the name of the faction. It doesn't have to be unique. However, its not being unique could lead to issues when the player uses commands that refer to the faction's name, like `/f rel`, `/f join`, etc. Any strings are accepted, as long as the server owner would accept.
* `string $founder` is the lowercase name of the faction founder. This foundership is constant unless it is passed to a successor due to his inactivity. If the faction is server-owned, put "console" for that. This parameter only serves as a function to give automatic foundership to that player.
* `Rank[] $ranks` is an array of instances of `Rank`. A `Rank` object can be instantiated by:
```php
public function \pocketfactions\faction\Rank::__construct(int $id, string $name, int $perms);
```
  * `$id` is the internal ID of the rank. If you populate an empty array with instances of `Rank`, put the key in the array of the rank as `$id`.
  * `$name` is the human-readable name of the rank. Examples are "member", "official" and "founder".
  * `$perms` is an integer expressed by the permissions he has, as in `\pocketfactions\faction\Rank::P_*****`, combined using the bitwise `OR` operator.

* `int $defaultRankIndex` is the key of the rank in `$ranks` that is the rank a player has when he newly joins. For normal factions (factions created using `/f create` and recruit using `/f invite` or `/f join`), the default one is member.
* `\pocketfactions\Main $main` is an instance of the faction's main class. You can get it by `$server->getPluginManager()->getPlugin("PocketFactions")`, where `$server` is an instance of `\pocketmine\Server`, usually resolved by `$this->getServer()` or `\pocketmine\Server::getInstance()`.
* `Position|Position[] $homes` are the default homes of the faction. It is safe to just put `[]` (empty array) for that, since homes aren't always necessary.
* `string $motto` is the default motto of the faction. It can be left as blank.
* `bool $whitelist` is the boolean value of whether players have to be invited to join the faction. Default is `true`.
* `int $id` is the ID of the faction. Do not put a value for this parameter unless you know what you are doing.

After calling this function, your new faction object should be returned. This function would automatically register the faction to the faction list and the in-memory database for indexing.

## I want to get the object of an existing faction
Currently, there are several ways of getting a faction. There are four identifiers available:

* Search by faction ID (fastest way)
* Search by faction name
* Search by faction alike name (like how players are found with alike names)
* Search by a chunk claimed by a faction
* Search by faction's member (a player object must be provided)

## I want to add faction money
Faction is a subclass of xEcon entity. Refer to xEcon documentation for how to add money to xEcon entities.
