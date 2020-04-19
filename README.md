PayBeer
=======

Application d'administration pour le système de prépaiement du ChillOut.

# Installation

Installation de Docker et docker-compose :

**Arch Linux / Manjaro**
```
sudo pacman -S docker docker-compose
sudo gpasswd -a $USER docker
sudo systemctl enable --now docker
```

**Ubuntu**
```
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
sudo usermod -aG docker $USER
sudo apt-get install docker-compose
```

Récupération du projet :
```
git clone git@github.com:HEIGVD-PRO-A-10/PayBeer.git
cd PayBeer
```

Lancement des conteneurs :
```
make up
```

Installation des dépendances :
```
make dep
```

Création des tables dans la base de données :
```
make migrate
```

Remplissage de la bd avec des données de test :
```
make fixtures
```

Accéder aux applications :

| Application | Lien                                           |
|-------------|------------------------------------------------|
| Symfony     | [http://localhost:8000](http://localhost:8000) |
| phpMyAdmin  | [http://localhost:8080](http://localhost:8080) |
| MailHog     | [http://localhost:8025](http://localhost:8025) |

Stop les conteneurs :
```
make stop
make clean # Optionnel, supprime les conteneurs
```
