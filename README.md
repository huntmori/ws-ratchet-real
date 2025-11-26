### server
```shell
php .\public\server.php
```
---
### client
```shell
cd public
php -S localhost:3000
```

---
docker-compose
```shell
docker-compose up -d
```
```shell
docker-compose down -v 
```
```shell
docker-compose build --no-cache app
```

---
# 

### SAMPLE JSON DATA(FOR TEST)
RoomCreate
```JSON
{
  "event_name": "room.create",
  "payload": {
    "room_name": "PUBLIC ROOM",
    "maximum_users": 8,
    "join_type": "PUBLIC",
    "open_type": "PUBLIC"
  }
}
```
Room Message
```JSON
{
  "event_name": "room.chat",
  "payload": {
    "room_uuid": "cd0f34b1-ca95-11f0-91f1-ca3eeb8c5813",
    "message" : "ratchet, hello world!"
  }
}
```

RoomJoin
```JSON
{
  "event_name": "room.join",
  "payload": {
    "room_uuid": "ada21635-c6df-11f0-bf31-e25046673686",
    "room_password": null
  }
}
```

Room List
```JSON
{
  "command": "room_list",
  "data": null
}
```

UserCreate
```JSON
{
  "event_name": "user.create",
  "payload": {
    "id" : "kknd",
    "password": "1q2w3e"
  }
}
```

User Login
```JSON
{
  "event_name": "user.login",
  "payload": {
    "id": "kknd",
    "password": "1q2w3e"
  }
}
```