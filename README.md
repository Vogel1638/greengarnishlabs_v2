# GreenGarnishLabs

Eine moderne Webplattform für vegane Rezepte und Küchentipps.

## 🌱 Über das Projekt

GreenGarnishLabs ist eine interaktive Webplattform, die sich der veganen Küche widmet. Unser Ziel ist es, köstliche pflanzliche Rezepte und hilfreiche Küchentipps für alle zugänglich zu machen - von Anfängern bis zu erfahrenen Köchen.

## 🚀 Funktionen

- **Rezeptdatenbank**: Umfangreiche Sammlung veganer Rezepte
- **Kategorisierung**: Einfache Navigation durch Vorspeisen, Hauptgerichte und Desserts
- **Benutzerverwaltung**: Persönliche Profile mit Favoritenfunktion
- **Tipps & Tricks**: Nützliche Ratschläge für die vegane Küche
- **Responsive Design**: Optimiert für alle Geräte

## 💻 Technische Anforderungen

- PHP 7.4 oder höher
- MySQL 5.7 oder höher
- Apache Webserver
- mod_rewrite aktiviert
- XAMPP (empfohlen für lokale Entwicklung)

## 🛠 Installation

1. Klone das Repository:
   ```bash
   git clone https://github.com/Vogel1638/greengarnishlabs_v2.git
   ```

2. Kopiere die Dateien in dein XAMPP htdocs Verzeichnis:
   ```bash
   C:/xampp/htdocs/greengarnishlabs
   ```

3. Importiere die Datenbank:
   - Starte XAMPP und aktiviere Apache und MySQL
   - Öffne phpMyAdmin
   - Erstelle eine neue Datenbank "greengarnishlabs"
   - Importiere die SQL-Datei aus dem `database`-Ordner
   - Alternativ kannst du die Datenbank auch direkt aus dem GitHub-Repository importieren, da dort ein Export der Datenbank enthalten ist

4. Konfiguriere die Datenbankverbindung:
   - Öffne `src/includes/db.php`
   - Passe die Datenbankzugangsdaten an

5. Öffne die Website im Browser:
   ```
   http://localhost/greengarnishlabs
   ```

## 🔧 Konfiguration

Die wichtigsten Konfigurationsdateien:

- `src/includes/db.php`: Datenbankverbindung
- `.htaccess`: URL-Routing
- `src/config/config.php`: Allgemeine Einstellungen

## 👥 Benutzerverwaltung

Standardmäßig werden zwei Benutzerrollen unterstützt:
- **Benutzer**: Kann Rezepte ansehen und favorisieren
- **Administrator**: Kann Rezepte und Tipps verwalten

## 📝 Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Weitere Details findest du in der LICENSE-Datei.

## 📧 Kontakt

GreenGarnishLabs
Unterfeldhof 4
CH-8854 Galgenen
E-Mail: info@greengarnishlabs.ch
