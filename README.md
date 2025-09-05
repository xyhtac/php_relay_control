# php_relay_control
### Web interface for [USR-IOT-R16 16-channel electric relay board](https://www.amazon.in/USR-IOT-USR-R16-T-Industrial-Interface/dp/B01DEVRUNG)
Max.Fischer dev@monologic.ru


![Web Interface for IO 16-channel relay board](/io-web-board.png?raw=true "Web interface - IO board")

For detailed information regarding communication protocol and board integrated logic features refer to ![GPIO Controller command protocol](/PROTO.md "GPIO Protocol description")

### 1. Configure your peripheral credentials
> **Filename:** `config/credentials.json`
```json
{
    "ptz-cam": {
      "hostname": "__CamIP__",
      "username": "__CamUsername__",
      "password": "__CamPassword__"
    },
    "bms-controller": {
      "hostname": "__BmsControllerIP__",
      "password": "__BmsControllerPassword__"
    },
    "bms-sensor": {
      "hostname": "__SensorIP__",
      "community": "__SensorCommunity__",
      "oid": "1.3.6.1.4.1.25728.8900.1.1.2.1"
    },
    "door-controller": {
      "hostname": "__DoorControllerIP__",
      "username": "__DoorControllerUsername__",
      "password": "__DoorControllerPassword__"
    }
  }
  
```

### 2. Configure relay channels
> **Filename:** `config/channels.json`
```json
[
  {
    "id": 16,
    "description": "Recuperation pump",
    "invert": false,
    "icon": "fa-refresh"
  },
  {
    "id": 15,
    "description": "Irrigation",
    "invert": true,
    "icon": "fa-envira"
  },
  {
    "id": 14,
    "description": "Well Pump",
    "invert": false,
    "icon": "fa-tint"
  },
  {
    "id": 13,
    "description": "Drain Pump",
    "invert": false,
    "icon": "fa-bitbucket"
  }
]


```

### 3. Configure custom actions
> **Filename:** `config/actions.json`
```json
[
  {
    "description": "Main Gate",
    "icon": "fa-lock",
    "url": "door.php?door_id=6",
    "button": "activate"
  },
  {
    "description": "Garage gate",
    "icon": "fa-lock",
    "url": "door.php?door_id=4",
    "button": "activate"
  },
  {
    "description": "Facade gate",
    "icon": "fa-lock",
    "url": "door.php?door_id=2",
    "button": "unlock"
  }
]
```
