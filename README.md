# Dashboard LXC / Minetest Debian

## 📋 Présentation
Ce dépôt explique comment installer des serveurs Minetest sur des conteneurs LXC ainsi qu'un dashboard interactif pour les gérer.

---

## Installation de Minetest

### Prérequis
- Conteneurs LXC configurés (adresses IP, etc.)
- Debian/Ubuntu sur les conteneurs

### Installation
1. Installez le serveur Minetest et les paquets pour la suites via APT :
```bash
   apt update
   apt install minetest-server iptables iptables-persistent fail2ban
```

2. Déplacez les fichiers :
   - 'minetest.conf' de chaque map dans `/etc/minetest/` de son conteneurs
   - 'world.mt' dans `/var/games/minetest-server/.minetest/worlds/world`.
   - 'sudoers' dans `/etc`.
   - La jail et le filtre 'minetest-auth.conf' dans `/etc/fail2ban`.
   - Les fichier index de Dashboard et Web dans `/var/www`.
   

4. Définissez les permissions appropriées :
```bash
   chown -R Debian-minetest:games /etc/minetest
   chown -R Debian-minetest:games /usr/share/games/minetest
   chown -R Debian-minetest:games /var/games/minetest-server
```

4. Redémarrez le service :
```bash
   systemctl restart minetest-server
```

---

## Configuration du DNAT

Pour rendre chaque map accessible depuis l'extérieur, configurez des règles DNAT sur votre serveur principal vers chaque conteneur, EX :
```bash
iptables -A PREROUTING -t nat -p udp -m udp --dport 30000 -j DNAT --to-destination 10.0.3.10:30000
iptables -A PREROUTING -t nat -p udp -m udp --dport 30001 -j DNAT --to-destination 10.0.3.15:30000
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

1. Modifiez la configuration Apache dans `/etc/apache2/sites-available/000-default.conf` :
```apache
   DocumentRoot /var/www/minetest
```
La où vous avez mis le fihcier 'index.php', ici minetest/.


2. Définissez les permissions :
```bash
   chown -R www-data:www-data /var/www/minetest
```

3. Redémarrez Apache :
```bash
   systemctl restart apache2
```
4. Faites pareil pour index.html de `Web`.

   
### 3. Personnalisation
Modifiez le fichier `index.php` selon vos besoins.

### Aperçu
<img width="1890" height="958" alt="image" src="https://github.com/user-attachments/assets/54331eb0-f399-4caa-9f4f-6c5bede1134f" />

<img width="1887" height="628" alt="image" src="https://github.com/user-attachments/assets/dec1c506-d9ff-47cc-b87b-95efe32bf2de" />

<img width="1887" height="961" alt="image" src="https://github.com/user-attachments/assets/f337a708-ad18-40fa-bddc-6852e90db7de" />

<img width="1889" height="466" alt="image" src="https://github.com/user-attachments/assets/c5ee8f8a-e69e-4f05-ba85-ec84b62b09f9" />

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
