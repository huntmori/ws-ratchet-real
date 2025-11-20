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
  "command": "room_create",
  "data": {
    "room_name" : "test",
    "join_type": "OPENED",
    "room_type": "GROUP"
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
  "event_name": ":user.create",
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