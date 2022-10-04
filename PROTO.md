# GPIO Controller command protocol

### General:
Send command: packet head length ID command parameter parity, ID usually used for RS485, only use command+parameter in network status
```
packet head(2)    |   length(1)      |   ID(1)   |   Command (1)   |   Parameter (n)   |   parity(1)
------------------------------------------------------------------------------------------------------------------------
0x55 0xaa         | n+2, length      |    id     |       C         |       xxxxxx      | Length (including)
                  |                  |           |                 |                   | Start to the end of parameters,
                  |                  |           |                 |                   | accumulation and parity
------------------------------------------------------------------------------------------------------------------------
                  |                 parity including area                              |
                  
  
```
Respond command: packet head length ID command parameter parity, ID usually used for RS485, only use command+parameter in network status
```
packet head(2)    |   length(1)      |   ID(1)   |   Command (1)   |   Parameter (n)   |   parity(1)
------------------------------------------------------------------------------------------------------------------------
0xaa 0x55         | n+2, length      |    id     |       C         |       xxxxxx      | Length (including)
Note: respond     |                  |           |                 |                   | Start to the end of parameters,
packet is         |                  |           |                 |                   | accumulation and parity
different from    |                  |           |                 |                   |
sending packet    |                  |           |                 |                   |
------------------------------------------------------------------------------------------------------------------------
                  |                 parity including area                              |
                  
  
```
1. If it is very stable when used for network communication or serial port communication, can use [command parameter] to simplify communication. But this may cause miscalculation as will not know parameters.
2. The respond data, can almost judge the condition of send data according to the respond content. And update the display of control interface, without record the control command it send, this also means that the module can be active to response data.
3. Assume that, all IO ports from 1 to N channel, N channel in total, if there is a leap, blank area fill 0.
4. No need all the products support all protocols, different product implement different protocol
5. General commands: 0xff+cmd. If module does not support present command, it will respond 0xff+cmd
