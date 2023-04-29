# biblioFRD-Server

Backend de la app de biblioteca realizado con Slim Framework 3.

## Instalación

El proyecto es instalable a través de Docker Compose.

    git clone https://github.com/brunosagaste/biblioFRD-Server && cd biblioFRD-Server
    docker compose up --build

Compose realizará las siguientes tareas:

* Copiar el archivo `/apache2conf/000-default.conf` a `/etc/apache2/sites-available/`.
* Instalar Composer e instalar las dependencias del proyecto.
