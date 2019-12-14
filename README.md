Záloha databáze pouze pomocí SSH a PHP
==============

Instalace
------
Stáhnete soubor `export.php`, ten otevřete pomocí příkazu `php`. __!!NE V PROHLÍŽEČI!!__
Poté pokračujete podle instrukcí.

Chyby
-------
Pokud dostanete chybu `Segmentation fault` nebo podobnou, musíte povolit ve vašem systému podporu UTF-8 (LINUX).
### Návod na povolení podpory UTF-8
1. `apt install locales`
2. Pokud nám vyběhne okno, najdeme v něm pomocí šipky DOLŮ `cs_CZ.UTF-8 UTF-8`
3. Pokud se nám žádné okno neobjeví, zadáme `sudo dpkg-reconfigure locales` a postupujeme podle kroku 2
4. Po vybrání `cs_CZ.UTF-8 UTF-8` vybereme `cs_CZ.UTF-8` a vyčkáme na konfiguraci
5. V připadě používání PuTTY, nebo jiného SSH klienta ho vypneme a opět zapneme, v případě linux systému se odhlásíme a opět přihlásíme
6. Mělo by vše fungovat
