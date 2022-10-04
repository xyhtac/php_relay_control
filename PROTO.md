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

Description of continuous values to show IO, with 12 relay as example

```
Byte Data    |  Byte 1                                       |  Byte 2
--------------------------------------------------------------------------------------------------------------------
channel      |  1  |  2  |  3  |  4  |  5  |  6  |  7  |  8  |  9  | 10  |  11  |  12  |  13  |  14  |  15  |  16  |
--------------------------------------------------------------------------------------------------------------------
data         |Bit 0|Bit 1|Bit 2|Bit 3|Bit 4|Bit 5|Bit 6|Bit 7|Bit 8|Bit 9|Bit 10|Bit 11|Bit 12|Bit 13|Bit 14|Bit 15|
--------------------------------------------------------------------------------------------------------------------
example      |  1  |  0  |  0  |  0  |  1  |  1  |  1  |  0  |  1  |  0  |  0   |   1  |   0  |   0  |   1  |   0  |
--------------------------------------------------------------------------------------------------------------------
             |  0xF1                                         |  0x03, blank area fill 0
                  
```
Form1: continuous IO instructions (pls refer to this form for following description of BBBB)

There are two ways to send commands to the hardware devices:
1. As TCP client connect device TCP server directlyl, port 8899, hardware IP address through search protocol to search it.
2. When send instructions to cloud server, it is need corresponding user name, passport and MAC address, pls see detial for realted file.

Notice: Local to control device, after build TCP connect, it need send passport +0x0D+0x0A, system respond OK or NO, that’s mean passport right or wrong, when passport is right, then it can go on work.

Below is only for [command, parameter]

When single-microcontroller receive command data, if the system is busy, it will return 0x7F 0x7F, other causes for treating failure will return 0x00 0x00

In commands, N represent a byte, usually selector channel. D represents a byte, means value. BBBB represents variable length and connected multiple bits. H is high, L is low

###1. Output command
1. 0x01 N clear(close) single IO
Return: 0x81 N 0
Example: send 0x01 0x01 return 0x81 0x01 0x00 means clear the first channel IO, the scopeof N is 1-255
2. 0x02 N set(open) single IO
Return: 0x82 N 1
3. 0x03 N invert single IO
Return: 0x83 N 1/0
4. 0x04 no parameter clear all output IO
Return: 0x84 0
5. 0x05 no parameter set all output IO
Return: 0x85 1
6. 0x06 no parameter invert all output IO
Return: 0x86 BBBB，BBBB means all IO current status
BBBB refer to form1 continuous IO instructions
7. 0x07 BBBB select multiple channel clear
Return: 0x87 BBBB
BBBB means the relay be selected to clear
8. 0x08 BBBB select multiple channel set
Return: 0x88 BBBB
BBBB means the relay be selected to set
9. 0x09 BBBB select multiple channel invert
Refund: 0x89 BBBB
BBBB means all IO current status
10. 0x0a no parameter reading all output IO status, no perform action
Return: 0x8a BBBB
BBBB means all output IO current status
11. 0x0b BBBB set all output IO status
Return: 0x8b BBBB
BBBB means all output IO status after the command execution

### 2. Input IO command
IO type and default output setting command
1. 0x10 BBBB set multiple IO as input type
Return: 0x90 BBBB current IO status
Save IO type at the means time, effect immediately
2. 0x11 BBBB set multiple IO as output type
Return: 0x91 BBBB current IO status
Save IO type at the means time, effect immediately
3. 0x12 BBBB set multiple output IO default value
Return: 0x92 BBBB current IO status
Save IO default value, effect when power on again
4. 0x14 no parameter read all input poet IO status, no perform action
Return: 0x94 BBBB
BBBB means all input IO current status
Note: Input and output IO universal command
1. 0x13 N read IO port current status
Return: 0x93 N 1/0
Special: Device can initiatively send this command to notify application, the current status has changed, this change is likely to be caused by external input, also may be caused by the program automatic control logic.
