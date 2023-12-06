<p align="center">
  <a href="https://amwal.tech/?utm_source=github&utm_medium=logo" target="_blank">
    <img src="https://uploads-ssl.webflow.com/62294ce746440b7bc08b4fc5/624352eb48193d537d329386_1-2-p-500.png" alt="Amwal" width="180" height="60">
  </a>
</p>

# Amwal dev tools
This repository contains the dev tools used by Amwal team.

### Plugins used:
- https://github.com/vimeo/psalm
- https://github.com/phpstan/phpstan
- https://github.com/php-cs-fixer/shim
- https://github.com/ergebnis/composer-normalize
- https://github.com/kubawerlos/composer-smaller-lock
- https://github.com/nektos/act

### Act with Phpstorm
#### Setting Up External Tools:
1. Go to Settings > Tools > External Tools.
2. Click + to add a new tool.
3. Name it (`GitHub Actions (act)`).
4. Set Program to your `act` executable path or just act if it's in your system `PATH`.
5. Set Arguments to the commands you typically use, like `-P ubuntu-latest=catthehacker/ubuntu:act-latest`.
6. Optionally, set the Working directory to `$ProjectFileDir$/app/code/Amwal/Payments/`.
7. Now, you can run act from Tools > External Tools > GitHub Actions (act).
