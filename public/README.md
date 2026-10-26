# TaskFlow Pro

TaskFlow Pro is a small web app I built to keep track of my work. I create a project, add tasks to it, give each task a priority and a due date, and then see everything on one dashboard and on a monthly calendar.

I made it to practise building a full web app from scratch with PHP and PostgreSQL, and to learn how Docker lets an app run the same way on any computer.

## What it does

| Page | What you can do there |
|---|---|
| Dashboard | See your total projects and tasks, how many are done and how many are still pending. The next five open tasks are listed at the bottom |
| Projects | Create a project with a name and an optional description. Each one gets a coloured tile with its first letter |
| My Tasks | Add a task to a project, tick it off when it's done, delete it, or filter the list by open, completed or all |
| Calendar | See every task on the day it's due. Move between months, jump back to today, or click a date to add a task for that day |

A few small details I liked adding:

- Overdue tasks show their date in red.
- Completed tasks are crossed out.
- Each task keeps the colour of the project it belongs to, so the calendar is easy to read at a glance.

## How it works

The app has three parts that work together:

```
Browser  →  Web server (PHP + Apache)  →  Database (PostgreSQL)
```

When you open a page, the web server runs the PHP code for it. That code reads from or writes to the database and sends the finished page back to your browser.

The web server and the database each run in their own Docker container. Docker Compose starts both with a single command, so there is nothing to install except Docker itself.

## What's inside

| Part | Tool |
|---|---|
| Web server | PHP 8.2 on Apache |
| Database | PostgreSQL 16 |
| Packaging | Docker and Docker Compose |
| Front end | Plain HTML, CSS and a little JavaScript |

### Database tables

The two tables are created automatically the first time the app starts.

| Table | What it stores |
|---|---|
| `projects` | Name, description, colour and the date it was created |
| `tasks` | Title, status, priority, due date and the project it belongs to |

Each task can be in one of three states, and has one of three priorities:

| Status | Priority |
|---|---|
| todo | low |
| in progress | medium |
| completed | high |

If a project is deleted, all of its tasks are deleted with it.

## Project structure

```
taskflow-pro/
├── Dockerfile          Builds the PHP and Apache image
├── compose.yaml        Starts the web and database containers together
├── .env.example        Template for the database settings
├── app/
│   ├── config/
│   │   └── database.php    Connects PHP to PostgreSQL
│   └── layout.php          Shared sidebar and page header
├── database/
│   └── init.sql            Creates the tables on first start
└── public/                 Everything the browser can open
    ├── index.php           Dashboard and project creation
    ├── tasks.php           Add, tick off, delete and filter tasks
    ├── calendar.php        Monthly calendar
    └── assets/
        ├── css/            Styling
        └── js/             Small scripts, such as opening the new project form
```

I kept the `app` folder outside `public` on purpose. Anything inside `public` can be opened from the browser, so the database settings live outside it.

## Run it on your computer

You only need Docker and Docker Compose installed.

**1. Clone the repository**

```
git clone https://github.com/SYED-SUMAID/taskflow-pro.git
cd taskflow-pro
```

**2. Create your settings file**

```
cp .env.example .env
```

Open `.env` and set a password of your own:

| Setting | Value |
|---|---|
| `DB_HOST` | `db` |
| `DB_NAME` | `taskflow` |
| `DB_USER` | `taskflow_user` |
| `DB_PASSWORD` | Any password you choose |

**3. Start the app**

```
docker compose up --build
```

The first start takes a few minutes while Docker downloads PHP and PostgreSQL.

**4. Open it in your browser**

Go to `http://localhost:8082`.

## Handy commands

| What you want | Command |
|---|---|
| Start the app in the background | `docker compose up -d` |
| Stop the app and keep your data | `docker compose down` |
| Stop the app and wipe the database | `docker compose down -v` |
| See what the app is printing | `docker compose logs` |

The code folders are shared with the container, so after you edit a PHP or CSS file you only need to refresh the browser.

![alt text](<Screenshot From 2026-10-26 12-19-51.png>)