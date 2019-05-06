## Parrot Tunes (Open Web Media Library and Player) ##

# Application Details

http://apps4net.eu/?page_id=9

# Screenshots

http://apps4net.eu/?page_id=81

#Installation guide

- You must have docker installed (https://www.docker.com/)
- Clone the code

``
git clone https://github.com/ikiranis/OWMP.git
``

- Copy **.env.sample** file to **.env**
- Edit **.env** file Change it with your settings
- Start docker containers

``
docker-compose up -d
``

- Now, you must have 2 containers running. _mysql-server and _apache-server. Check it with

``
docker ps
``

- At your browser go to **http://localhost:<your_port>** or **http://<your_ip>:<your_port>**

# Support the app

http://apps4net.eu/?page_id=58
