# Parrot Tunes (Open Web Media Library and Player)

## Application Details

https://apps4net.eu/parrot/

## Installation guide

- You must have docker installed (https://www.docker.com/)
- Clone the code

``
git clone https://github.com/ikiranis/OWMP.git
``

- Copy **.env.sample** file to **.env**
- Edit **.env** file. Change it with your settings
- Start docker containers

``
docker-compose build && docker-compose up -d
``

- Now, you must have 2 containers running. _mysql-server and _apache-server. Check it with

``
docker ps
``

## Update

Warning: First, take a backup of the database!

```
docker-compose down
git pull
docker-compose build --no-cache && docker-compose up -d
```

- At your browser go to **http://localhost:<your_port>** or **http://<your_ip>:<your_port>**

## Support the app

http://apps4net.eu/?page_id=58
