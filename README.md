# Dashboard LXC / Minetest Debian

## 📋 Présentation
Ce dépôt explique comment installer des serveurs Minetest sur des conteneurs LXC ainsi qu'un dashboard interactif pour les gérer.

---

## Installation de Minetest

### Prérequis
- Conteneurs LXC configurés (adresses IP, etc.)
- Debian/Ubuntu sur les conteneurs

### Installation
1. Installez le serveur Minetest via APT :
```bash
   apt update
   apt install minetest-server
```

2. Déplacez les fichiers de configuration de chaque map dans `/etc/minetest/` :
   - `minetest.conf`
   - `world.mt`

3. Définissez les permissions appropriées :
```bash
   chown -R Debian-minetest:games /etc/minetest
```

4. Redémarrez le service :
```bash
   systemctl restart minetest-server
```

---

## Configuration du DNAT

Pour rendre chaque map accessible depuis l'extérieur, configurez des règles DNAT sur votre serveur principal vers chaque conteneur :
```bash
iptables -A PREROUTING -t nat -p udp -m udp --dport 30000 -j DNAT --to-destination 10.0.3.10:30000
```

> **Note :** Adaptez le port et l'adresse IP selon votre configuration.

---

## Installation du Dashboard

### 1. Installation d'Apache et PHP
Sur votre serveur principal, installez les dépendances nécessaires :
```bash
apt update
apt install apache2 php php-cli php-common libapache2-mod-php
```

### 2. Configuration du Dashboard

1. Créez le dossier pour le dashboard :
```bash
   mkdir -p /var/www/minetest
```

2. Déplacez le fichier `index.php` dans `/var/www/minetest`

3. Modifiez la configuration Apache dans `/etc/apache2/sites-available/000-default.conf` :
```apache
   DocumentRoot /var/www/minetest
```

4. Définissez les permissions :
```bash
   chown -R www-data:www-data /var/www/minetest
```

5. Redémarrez Apache :
```bash
   systemctl restart apache2
```

### 3. Personnalisation
Modifiez le fichier `index.php` selon vos besoins.

### Aperçu
<img width="1886" height="957" alt="Dashboard - Vue principale" src="https://github.com/user-attachments/assets/7e84cab3-c646-4dee-ba52-3af48074730e" />
<img width="1889" height="958" alt="Dashboard - Vue détaillée" src="https://github.com/user-attachments/assets/caf28b72-6633-4b0e-ae01-59fb64a8fe6a" />

---

## Installation des Scripts

1. Déplacez les scripts `.sh` dans `/usr/bin/`
2. Déplacez les fichiers `.service` dans `/etc/systemd/system/`
3. Rechargez systemd :
```bash
   systemctl daemon-reload
```
4. Appliquez les droits d'exécution pour l'utilisateur `www-data`

---

## Informations importantes

Ce dashboard est pleinement compatible avec les distributions disposant de :
- LXC (Linux Containers)
- Apache2
- PHP

---

## Contribution

Les contributions sont les bienvenues ! Vous pouvez :
- **Modifier** et **améliorer** le code
- **Proposer des mises à jour**
- Ouvrir une **issue** pour signaler un problème
- Soumettre une **pull request** pour vos améliorations

### Contact
- Email : **contact@nlempereur.ovh**
- Site web : https://nlempereur.ovh/contact.php

---

## Licence

Ce projet est sous **licence libre**.  
Vous êtes libre de l'utiliser, le modifier et le redistribuer selon vos besoins.

---

**Merci d'utiliser ce projet !**  
🔗 https://nlempereur.ovh
