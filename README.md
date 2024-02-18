
# Nuxt Blog


## Wstęp
Projekt opiera się na technologii Nuxt na froncie oraz Laravel jako backend. Blog umożliwia użytkownikom dodawanie, edytowanie oraz usuwanie artykułów, komentowanie wpisów, logowanie, rejestrację oraz edycję ustawień użytkowników. Dodatkowo dostępna jest funkcja wyszukiwania po tytułach postów, kategoriach oraz autorach. W projekcie byłem odpowiedzialny w projektowanie interfejsu użytkownika oraz wdrożenie aplikacji (backend oraz frontend).


## Główne założenia projektowe

 📄 dodawanie przez użytkowników nowych artykułów przez bagebuilder, działający na zasadzie przesuń i upuść

🧑  rejestracja, logowanie oraz edyacja włanych danych

🏷️  zapisywania interesującycych nas artukułów 
 
💬  dodawanie komentarzy do artykułów z możliwością dodawania reakcji do nich przez zalogowanych użytkowników 

✌️  podział artykułów na kategorie

🔎  wyszukiwarka po tytule artykułu, autorach oraz kategoriach

🧑‍🎨  nowoczesny design



### Wykorzystana technologia:
- Larabvel, PHP
- MySql
- breeze, sanctum
- Rest API


## Instalacja projektu

Instalowanie oraz uruchomienie apliacji; wymagane jest pobranie

```bash
# clone repo
git clone https://github.com/cyprianwaclaw/blog-backend.git

# composer
composer install

# change .env file
cp .env.example .env

# generate new laravel key
php artisan key:generate

# change database configuration and migrate 
php artisan migrate

# run developer server
php artisan serve

Start the development server on `http://localhost:8000`:

```
