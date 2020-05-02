# Contributing to yape-cli

First, I would like to thank you for taking the time to consider contributing to this project! I appreciate you looking 
at and using the code I have developed. To ensure that your contributions are accepted I recommend you follow the below 
guidelines.

## Open an RFC

If you're going to modify or add to the code in some important way you should open a GitHub Issue and label it with 'rfc' 
or simply open a Pull Request. If the change is substantial and is likely to require community consensus we recommend you 
open an Issue and describe the changes before major developments. This way you're less likely to put an investment into 
something that may not be accepted. If the change is small or is an obvious bug fix please feel free to simply submit a 
Pull Request directly.

## Everything is tested

While I do not require a strict 100% code coverage it is important that all new features and bugs are well tested. 
Generally speaking a PR will not be accepted until it has good tests written for it.

## Keep it in Scope

This library is intended to provide support for generating yape `Enum` and `AbstractEnumType` implementations provided by 
cspray/yape and cspray/yape-dbal, respectively. The base functionality for this library is in place but more features and 
improvements that make generating enums even easier are appreciated.
