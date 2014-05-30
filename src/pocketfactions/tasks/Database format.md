PocketFactions Database Format:
===
The following is a brief of the database format of PocketFactions.

## Definition of data types
| Category | Name | Explanation | Range | Length (byte(s)) |
| :---: | :---: | :---: | :---: | :---: |
| Text | `char` | A character saved directly | any one-byte character | 1 |
| Number | `byte` | A number saved as one byte, unsigned | numbers from 0 to 255 | 1 |
| Boolean + Number | `CompoundByte` | A boolean at the first bit + A number at the last seven bits | `true` or `false` + Any numbers from 0 to 127 | 1 |
| Number | `short` | A number saved as two bytes, unsigned | numbers from 0 to 65535 | 2 |
| Number | `int` | A number saved as four bytes, unsigned | number from 0 to 4294967295 | 4 |
| Number | `long` | A number saved as eight bytes, unsigned | number from 0 to 18446744073709551615 | 8 |
| Boolean | `ByteBool` | A group of 8 booleans | any matches of 8 booleans | 1 |
| Boolean | `ShortBool` | A group of 16 booleans | any matches of 16 booleans | 2 |
| Text | `RawString` | A string saved directly | Any strings | The number of characters in the string |
| Text | `ByteString` | A string saved prefixed with a `byte` of string length | Any strings of not more than 255 characters long | 1 + The number of characters in the string |
| Text | `ShortString` | A string saved prefixed with a `short` of string length | Any strings of not more than 65535 characters long | 2 + The number of characters in the string |
| Boolean + Text | `CompoundString` | A string prefixed with a `CompoundByte` of a boolean description of the string and string length | `true` or `false` + Any strings of not more than 127 characters long | 1 + The number of characters in the strsing |

## Format
```
RawString MAGIC PREFIX at \pocketfactions\Main::MAGIC_P (16 bytes long)
char Database version
int Count of saved factions
-> for each faction:
    int Unique faction ID
    CompoundString A boolean whether the faction is whitelisted (only invited players can join) + The faction name
    ShortString Faction motto
    ByteString Founder of the faction
    byte Count of faction internal ranks
    -> for each rank:
        byte Internal ID of the internal rank
        ByteString Name of the rank
        ShortBool Permissions of the faction, with reference to [rank.php](../Rank.php)
    byte Internal ID of the default internal rank
    int Number of members in the faction
    -> for each member:
        ByteString Lowercase name of the member
        byte Internal ID of the rank of the member
    short Number of chunks claimed by the faction
    -> for each chunk:
        short The X-index of the claimed chunk in the world
        short The Z-index of the claimed chunk in the world
        ByteString The world name of the chunk
RawString MAGIC SUFFIX at \pocketfactions\Main::MAGIC_S (16 bytes long)
```

## Chunks
Each chunk is saved with the following three elements:
* X
* Z
* World name

On each loading of database and creation of faction, if no world of the world name is loaded, it is loaded. If no world of the world name exists, it is generated.

In each world, the default spawnpoint is at (0, y, 0). Counting along the X-axis and the Z-axis, the first chunk seen is 0, the second is 1, vice versa. If counted reversed, the first chunk seen is -1, the second is 2, and so on.
