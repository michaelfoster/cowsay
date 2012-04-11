php-cowsay
==========

cowsay is a configurable talking cow, written in Perl in 1999/2000. This project is a quick translation of that script into PHP.

Usage
-----
    % php cowsay.php -h
    php-cowsay version 1.0, (c) 2012 Michael Foster
    Usage: cowsay.php [-bdgpstwy] [-h] [-e eyes] [-f cowfile]
              [-l] [-n] [-T tongue] [-W wrapcolumn] [message]

It should act in exactly the same way as the origina cowsay, so consult `cowsay(1)` for help.

    % echo 'Hello, world!' | php cowsay.php 
     _______________
    < Hello, world! >
     --------------- 
            \   ^__^
             \  (oo)\_______
                (__)\       )\/\
                    ||----w |
                    ||     ||

