PocketFactions Database Format:
===
The following is a brief of the database format of PocketFactions.

## Definition of data types
| Category | Name | Explanation | Range | Length (byte(s)) |
| :---: | :---: | :---: | :---: | :---: |
| Text | `char` | A character saved directly | any one-byte character | 1 |
| Number | `byte` | A integer saved as one byte, unsigned | numbers from 0 to 255 | 1 |
| Boolean + Number | `CompoundByte` | A boolean at the first bit + A number at the last seven bits | `true` or `false` + Any numbers from 0 to 127 | 1 |
| Number | `short` | A integer saved as two bytes, unsigned | numbers from 0 to 65535 | 2 |
| Number | `int` | A integer saved as four bytes, unsigned | number from 0 to 0xFFFFFFFF | 4 |
| Number | `SignedInt` | An `int` showing how much larger the integer is from -0x80000000 | integers from -0x80000000 to 0x7FFFFFFF | 4
| Number | `long` | A number saved as eight bytes, unsigned | number from 0 to 0xFFFFFFFFFFFFFFFF | 8 |
| Number | `SignedLong` | An `Long` showing how much larger the integer is from -0x8000000000000000 | integers from -0x8000000000000000 to 0x7FFFFFFFFFFFFFFF | 8
| Boolean | `ByteBool` | A group of 8 booleans | any matches of 8 booleans | 1 |
| Boolean | `ShortBool` | A group of 16 booleans | any matches of 16 booleans | 2 |
| Boolean | `IntBool` | A group of 16 booleans | any matches of 32 booleans | 4 |
| Text | `RawString` | A string saved directly | any strings | The number of characters in the string |
| Text | `ByteString` | A string saved prefixed with a `byte` of string length | any strings of not more than 255 characters long | 1 + The number of characters in the string |
| Text | `ShortString` | A string saved prefixed with a `short` of string length | any strings of not more than 65535 characters long | 2 + The number of characters in the string |
| Boolean + Text | `CompoundString` | A string prefixed with a `CompoundByte` of a boolean description of the string and string length | `true` or `false` + Any strings of not more than 127 characters long | 1 + The number of characters in the string |
| Number + Text | `Position` | A position (x, y, z, level name) saved as two `SignedInt`s for X and Z chunk indices, one `byte` with the first four bits for x-coord inside the chunk and last four bits z-coord inside the chunk, one `short` for y-coord and one `ByteString` | any walk-in-able valid positions in any worlds of names not more than 255 characters long | 12 + The number of characters in the world name |

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
        IntBool Permissions of the faction, with reference to [rank.php](../Rank.php)
    byte Internal ID of the default internal rank
    int Number of members in the faction
    -> for each member:
        ByteString Lowercase name of the member
        byte Internal ID of the rank of the member
    long last active timestamp
    short Number of chunks claimed by the faction
    -> for each chunk: (the base chunk is the first chunk shifted)
        SignedInt The X-index of the claimed chunk in the world
        SignedInt The Z-index of the claimed chunk in the world
        ByteString The world name of the chunk
    Position The home position
long number of faction relationships
    -> for each faction relationship:
        int ID of faction 0 in the relationship
        int ID of faction 1 in the relationship
        byte state
RawString MAGIC SUFFIX at \pocketfactions\Main::MAGIC_S (16 bytes long)
```

## Chunks
Each chunk is saved with the following three elements:
* X
* Z
* World name

On each loading of database and creation of faction, if no world of the world name is loaded, it is loaded. If no world of the world name exists, it is generated.

In each world, the default spawnpoint is at (0, y, 0). Counting along the X-axis and the Z-axis, the first chunk seen is 0, the second is 1, vice versa. If counted reversed, the first chunk seen is -1, the second is 2, and so on.
