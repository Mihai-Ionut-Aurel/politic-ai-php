## Team
This project was part of the ICT Entrepreneurship course at Utrecht University. The course acts as an early 
incubator and assists team of 2 to 4 students at building and ICT business in 10 weeks. The politicai.nl team was compose of

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
|[<img src="https://media.licdn.com/dms/image/C4E03AQGdW4_nQEHYgg/profile-displayphoto-shrink_800_800/0?e=1536192000&v=beta&t=Nq7XEs6gEHnrGg3iLNIYHiRLdABQuq7sM0f_wGhPMmc" width="100px;"/><br /><sub>Richard Ooms</sub>][richard-profile]
|[<img src="https://media.licdn.com/dms/image/C4D03AQH_OUE71xvvMQ/profile-displayphoto-shrink_800_800/0?e=1536192000&v=beta&t=s6aNkiq60G-yYHPcxAijYb0nYKfwOHksa9HRGh179SA" width="100px;"/><br /><sub>Jordan van Dijk</sub>][jordan-profile]<br />|
 [<img src="https://media.licdn.com/dms/image/C4D03AQFlu45SxA2NUA/profile-displayphoto-shrink_800_800/0?e=1536192000&v=beta&t=BNPCcAqp5-gmwEciu4KdpJaMMfvc7hkja0ZYTnSBi1s" width="100px;"/><br /><sub>Yannick Mijsters</sub>][yannick-profile]|
  [<img src="https://media.licdn.com/dms/image/C4E03AQFioaA-kF770Q/profile-displayphoto-shrink_200_200/0?e=1536192000&v=beta&t=M5dO8wJUWutlCwLNnpWQkHPWymhb5elJnTdUdee8d6A" width="100px;"/><br /><sub>Anuradha Gunasekara</sub>][mihai-profile]|
| :---: | :---: | :---: | :---: |

Besides us, the team is composed of all the people who gave us their help. We wanna thank them for their help and support.
- Slinger Roijackers
- Sjaak Brinkkemper
- The team of Gracenote NWG
-
## Project info

Index data on politics and refines into an intelligent search engine for journalists. This is a first part of the project.
Besides this, there is this github project which handles the text analysis tasks.

## Instructions
To run locally install Docker from [download here](https://docs.docker.com/toolbox/toolbox_install_windows/).
To run the application execute the command `docker-compose up`. After that, you can connect to the docker machine ip to see the project.
Useful commands:
- Stop all running containers. `docker stop $(docker ps -aq)`
- Remove all containers. `docker rm $(docker ps -aq)`
- Remove all images. `docker rmi $(docker images -q)`
- To ssh into a container follow the steps:
    - Find container name with : `docker ps -aq`
    - Find the container name that you want to ssh. Use the first 3 letters and execute: `docker exec -it <container name> /bin/bash`
    
More details on Docker commands and syntax at: https://docs.docker.com/.

### Windows
For windows you are most likely to use the DockerToolbox to run the application. For the project to work you 
need to place it in a folder which path starts with _C:/Users/<YourUsername>/_ .This has to be done because 
the project mounts the project folder into a volume into the Docker container. On Windows, the virtual machine can
access only folders that start with _C:/Users/<YourUsername>/_
The Docker container doesnt have access rights for folders in other locations or partitions.

##Workflow
The project will follow the normal git workflow.



[mihai-profile]: https://www.linkedin.com/in/mihaiionutaurel/
[richard-profile]: https://www.linkedin.com/in/richardljooms/
[yannick-profile]: https://www.linkedin.com/in/yannick-mijsters-264244135/
[jordan-profile]: https://www.linkedin.com/in/jordan-van-dijk-874a48137/