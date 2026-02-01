# Docker installer
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)

## Projektstruktur

- `db/`: Indeholder SQL-scripts til at initialisere databasen.
- `src/`: Indeholder PHP-kode.
- `docker-compose.yml`: Definition af services (PHP 8.2 (Apache) og MySQL 8.0.).
- `Dockerfile`: Tilpasning af PHP-imaget (installerer nødvendige extensions).


## Sådan kører du projektet

1. Åbn din terminal i projektets rodmappe.
2. Kør følgende kommando for at starte containerne:

   ```bash
   docker compose up -d
   ```

3. Åbn din browser og gå til: [http://localhost:8080](http://localhost:8080)
4. Hvis du ser teksten "Velkommen" køre projektet som det skal

## Stop projektet

For at stoppe og fjerne containerne skal du køre:

```bash
docker-compose down
```

# Postman Installation
- [Postman](https://www.postman.com/)
- import postman.json
- test endpoints