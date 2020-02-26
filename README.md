Záloha databáze pouze pomocí SSH a PHP
==============

Ukázka
-----
![Ukázka](https://upload.patrikmin.tech/screenshot/qbRfNcHuve)


Požadavky
------
- Testováno na php `7.3` a `7.4`
- Testováno na Debian GNU/Linux 9 (stretch)
- Mysqli addon k php
- Mysql/MariaDB s podporou commandu `mysqldump`

Nejčastější chyby
-------
Pokud dostanete chybu `Segmentation fault` nebo podobnou, musíte povolit ve vašem systému podporu UTF-8 (LINUX).
### Návod na povolení podpory UTF-8
1. `apt install locales`
2. Otevřeme soubor `/etc/locale.gen` a najdeme řádek `# cs_CZ.UTF-8 UTF-8`
3. Odebereme `# ` a soubor bude vypadat takto:
4. ![Jak bude vypadat soubor locale.gen](https://upload.patrikmin.tech/screenshot/bOdbTQV1cp)
5. Poté zadáme `sudo dpkg-reconfigure locales` a vybereme `cs_CZ.UTF-8 UTF-8`
6. Po vybrání `cs_CZ.UTF-8 UTF-8` vybereme `cs_CZ.UTF-8` a vyčkáme na konfiguraci
7. V připadě používání PuTTY, nebo jiného SSH klienta ho vypneme a opět zapneme, v případě linux systému se odhlásíme a opět přihlásíme
8. Mělo by vše fungovat

------

Použití
------
1. Stáhnete soubor zip.
2. Ten rozbalíme a přesuneme si soubor `export.php`, kam chceme.
3. Soubor `export.php` otevřeme pomocí příkazu `php export.php`.
__!!NE V PROHLÍŽEČI!!__
4. Poté pokračujete podle instrukcí.

Příkazy
------
### Nápověda:
- syntaxe: `php export.php help`

### Export pomocí příkazu:
- syntaxe: `php export.php export <použití configu> <databáze/host> <port> <jméno> <heslo> <databáze>`
- použití s údaji v commandu: `php export.php export false <host> <port> <jméno> <heslo> <databáze>`
- použití s uloženými údaji: `php export.php export true <databáze>`

**Příklady**:
- `php export.php export true xenforo`
- `php export.php export true xenforo,phpmyadmin`
- `php export.php export true all`
-
- `php export.php export false localhost root password123 xenforo`
- `php export.php export false 89.203.17.45 3306 root password123 xenforo,phpmyadmin`
- `php export.php export false 127.0.0.1 3458 root password123 all`

### Smazání configu (uložených údajů):
- syntaxe: `php export.php delconf`

### Uložení údajů:
- syntaxe: `php export.php saveconf <jméno> <heslo> <host> <port>`

**Příklady**:
- `php export.php saveconf root password123 localhost 3306`
- `php export.php saveconf web password123 82.204.68.48 3345`


