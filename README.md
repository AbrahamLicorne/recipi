# Recipi

Application full-stack avec un front React + Material UI et une API Symfony, orchestrée via Docker.

## Stack

- **Frontend** : React 19 (Vite + TypeScript) + Material UI
- **Backend** : Symfony 7 (PHP 8.3) — API JSON
- **Base de données** : PostgreSQL 16
- **Reverse proxy** : Nginx
- **Orchestration** : docker-compose

## Structure

```
recipi/
├── frontend/        # React + MUI (Vite)
├── backend/         # API Symfony
├── docker/
│   ├── nginx/       # vhost nginx
│   └── php/         # php.ini
└── docker-compose.yml
```

## Prérequis

- Docker + docker-compose
- (Optionnel) Node 20+ et PHP 8.3 / Composer si tu veux développer hors Docker

## Démarrage

```bash
docker compose up --build
```

Une fois les services up :

- Frontend : http://localhost:5173
- API : http://localhost:8080/api/ping
- Postgres : `localhost:5432` (user: `app`, password: `app`, db: `app`)

## Commandes utiles

```bash
# Console Symfony
docker compose exec backend bin/console <commande>

# Créer une migration Doctrine
docker compose exec backend bin/console make:migration
docker compose exec backend bin/console doctrine:migrations:migrate

# Installer une dépendance composer
docker compose exec backend composer require <package>

# Installer une dépendance npm
docker compose exec frontend npm install <package>

# Logs
docker compose logs -f backend
docker compose logs -f frontend
```

## Variables d'environnement

- `frontend/.env.example` → copier en `frontend/.env` si besoin
- `backend/.env` → valeurs par défaut (committé)
- `backend/.env.local` → overrides locaux non commités
