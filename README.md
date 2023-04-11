# LarpingBundle [![Codacy Badge](https://app.codacy.com/project/badge/Grade/cad45ffbc444440f8b61ac58a739bba3)](https://app.codacy.com/gh/LarpingBase/LarpingBundle/dashboard?utm_source=gh\&utm_medium=referral\&utm_content=\&utm_campaign=Badge_grade)

The larping bundle provides an full-length database solutions catered to persons and organisations running live action role playing event. It provides character management trough characters, skills, abilities, effect and conditions but also provides machanismes for setting up story arcs.

The larping bundle uses the common gateway project as a framework for its code.

## Prerequisites

To install the application locally you will need two applications

1.  [Docker Desktop](https://www.docker.com/products/docker-desktop/)
2.  [Git](https://git-scm.com/downloads)

Keep in mind that installing docker desktop can be a bit of a hussle, please make sure to follow the complete [installation instructions](https://docs.docker.com/desktop/install/windows-install/). De installation of git is a bit more straight foreward but wil require a system reboot.

## Local Installation

After making sure you meet the prerequisites, you can install larping on your local machine. This installes a application that is designed for servers so it can be a bit comberson

1.  Make a folder where you want to install the application
2.  Open an CLI window (like windows [CMD](https://www.makeuseof.com/tag/a-beginners-guide-to-the-windows-command-line/) or [powershell](https://learn.microsoft.com/en-us/powershell/scripting/overview?view=powershell-7.3))
3.  Navigate to the folder you created trough your CLI window, this can be done trough the ChangeDirertory command or for short `$ cd [your directory` e.g. `$ cd C:\Users\ruben\Documents\repositories\Larping`
4.  Clone the gateway repository `$git clone https://github.com/ConductionNL/commonground-gateway`
5.  Go into the gateway directory trough cd `$ cd commonground-gateway`
6.  Switch to the larping branche `$ git checkout feature/larping`
7.  Start the application trough `$ docker compose up  --build`

You should now see the containers of the application starting, when the container  commonground-gateway-php gives the status “fpm ready to handle connection”. You can start the application.

Go to `localhost:8000` in you favorite browser and log in

## Restarting the application

You can restart the application at any time by running the `$docker compose up ` command, building the application (trough –build) is only required when updating the application.

## Upgrading  the application

You can also upgrade the application from the command line, this requires the following commands to be carried out trough the CLI in your installation folder.

1.  git pull
2.  docker compose pull ui
3.  docker compose up -- build
