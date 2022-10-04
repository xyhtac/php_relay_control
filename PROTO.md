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


### 3. PWM port and frequency command
Note: PWM output without unit, can be 0-100 percent, also can be 0-255 RGB represent threeprimary colors
1. 0x20 read all PWM status
Return: 0xa0 DDDD current PWM value, each byte represent duty ratio of one channel
Such as: return 0xA0 0x01 0x30 means the first channel duty ratio is 1, the second channel duty ratio is 48
2. 0x21 DDDD set all PWM value
Return: 0xa1 DDDD current PWM value, each byte represent duty ratio of one channel
Such as: send 0x21 0x01 0x30 means set the first channel duty ratio is 1, set the second channel duty ratio is 48
3. 0x22 N D set specified channel duty ratio output N means operating channel, D is actual value
Return: 0xa2 N D, example: send 0x22 0x01 0x10 means set the first channel duty ratio 16
4. 0x23 N read specified channel duty ratio output N means operating channel
Return: 0xa3 N D, for example send 0x23 0x01, return 0xa3 0x01 0x10, means read and get
the first channel duty ratio is 16
5. 0x24N read all PWM duty ratio and frequency status
Return: 0xa4 DD DH DL…
6.0x25 DD DH DL… Set all PWM duty ratio and frequency status
Return: 0xa5 DD DH DL…
Each channel PWM have 1pcs bite duty ratio, 2pcs bite frequency, and several channel in turn arrangement.

### 4. Frequency operating command
Frequency operating command is in common with PWM operation command, but frequency parameter has two bytes, high in front and low behind
1. 0x30 read all frequency status
Return: 0xb0 DHDL DHDL current frequency value, every two bytes represent one channel frequency value
Example: return 0xB0 0x00 0x30 0x10 0x00 means the first channel frequency is 0*256+48=48, the second channel frequency is 16*256+0=256
2. 0x31 DHDL set all frequency value
Return: 0xb1 DHDL DHDL current frequency value, each two bytes represent one channel frequency value
Example: 0x31 0x01 0x30 0x00 0x20 means set the first channel frequency 1*256+48=304, set the second channel frequency 0+32=32
3. 0x32 N D set specified channel frequency value N means operating channel, DHDL means actual value
Return: 0xb2 N DH DL, for example send 0x22 0x01 0x10 0x02 means set the first channel frequency 16*256+2=4098
4. 0x33 N read specified channel frequency value N represent the operating channel
Return: 0xb3 N DH DL, for example, send 0x23 0x01, return 0xb3 0x01 0x10 0x02 means read and get the first channel frequency is16*256+2=4098


### 5. Register command
Each register data 2 bytes, showing the AD analog input and all the sensor data, top digit represent positive and negative, =1 means negative,=0 means positive, data part divided by 10 means, range from -3276.7 to +3276.7, example:
Receive 0x80 0x10 means: -1.6
Receive 0x01 0xaa means: +42.6
1. 0x40 read all register data
Return: 0xC0 DDDD ... Return all register data in turn
2. 0x41 N read single register data
Return: 0xC1 N DH DL single register data
3. 0x42 S N read specific register section data
Return: 0xC2 S N DH DL DH DL ... the specific register section data
4. 0x43N clear single register data
Back: 0XC3N clear register channel
5. 0x44 clear all register data
Back: 0xC4 0
Description: S one byte, means register initial address (0-255) N one byte, means register quantity (1-255)


### 6. Timing work command
The system defines a storage range of 55 byte, used to storage the 5 timing commands of 0~4, location and sequence fixed (5 points at present, based on system processing ability, can be more)
Note: You should use 0x70 commands to check if this function is available
```
    Class       |  Task ID      |  enable type                |   time              |   cmd                   |   Week enable
-------------------------------------------------------------------------------------------------------------------------------------------
Number of bytes |  1            |  1                          |   4                 |   4                     |   1
-------------------------------------------------------------------------------------------------------------------------------------------
instructions    | Start from1,  | The highest means enable or | Unix time stamps,   | Carry out the commands  | Bit 0~6 stand for Sun.
                | 1~5 max 5     | not, 1 is enable, default   | high order in front | in this list, usually   | to Sat. 1 allow action,
                |               | enable when add.            |                     | means out put control   | 0 not allow, highest
                |               | Low order means cyclical    |                     | command, the lacked     | bit pls fill 0
                |               | patterns, see note          |                     | bytes use 0             | 
-------------------------------------------------------------------------------------------------------------------------------------------
example         |  0x01         |  0x80                       | 0x51C8E925          | 0x05 00 00 00           | 0x7F
-------------------------------------------------------------------------------------------------------------------------------------------
meaning         | Timing task 1 | enable|single task          | 2013-06-25 08:49:41 | Output all open         | Allow action everyday
```
Note:
Cyclical type instructions: 0 single time, 1 minute circulate, 2 hour circulate, 3 day circulate, 4 month circulate. The system will adjust the timing time to the next time you need to perform according to cyclical patterns, after finish the timing task. Civil usage APP only think about single time and day circulate (choose which day to perform by week enable) is ok.
Unix timestamp: The seconds from Jan.1st, 1970(UTC/GMT midnight), not including leap seconds. Example: 0x51C8E925=1372121381=Jun25, 2013 08:49:41
Pls refer to http://shijianchuo.911cha.com/
1. 0x50 N read timing task list
Parameters: N: used to indicate reading N channel IO timing task, when N is 0, it means read all timing task, when N is not 0, it means read the timing task list for N channel IO. N from 0~255
Return: 0xD0 +the number of qualified task {less than or equal to 5, 0 is no list}+task list, followed by arrangement, format as the table above.
2. 0x51 add timing task
Parameters: Type Time CMD Week as the above table
Return: 0xD1 ID Type Time CMD Wee, ID means the location number after storage, FF means storage is full, storage failure
3. 0x52 M N single timing task enable disable delete
Parameters: M means the task number to operate 1~5M N means operation type 1 enable 2 disable 3 delete
Return: 0xD2 M N
4. 0x53 read system time
Parameters: no
Return: 0xD3 Time Time is Unix time stamp
5. 0x54 set system time
Parameters: Time
Return: 0xD4 Result Time
Result 1 means succeed, 0 means fail (it may not support or fail to set time because of hardware) Time is time stamp
