# native-php-framework

A framework that can build web application faster using native php advance 🚀

### 💻 Tech Stack

-   HTML, CSS, JS
-   Bootstrap
-   JQuery Ajax
-   PHP (OOP)
-   MySQL Database
-   Laragon Web Server

### 📂 Folder Structures

```
└── 📁api
    └── 📁auth
        └── auth.js
        └── auth.php
        └── login.php
        └── logout.php
    └── 📁profile
        └── update.php
└── 📁app
    └── 📁core
        └── components.php
        └── config.php
        └── database.php
        └── functions.php
        └── jwt.php
        └── settings.php
        └── utils.php
    └── init.php
└── 📁assets
    └── 📁css
        └── app.css
    └── 📁font
    └── 📁img
    └── 📁js
        └── app.js
    └── 📁logo
        └── favicon.ico
        └── icon.png
        └── main.png
        └── nav.png
    ├── plugin
└── 📁page
    └── 📁_component
        └── app.css
        └── app.js
        └── app.php
        └── Loader.php
        └── Sidebar.php
        └── Topbar.php
    └── 📁_template
        └── Footer.php
        └── Header.php
    └── 📁_utils
        └── app.php
    └── 📁profile
        └── Details.php
    └── 404.php
    └── Dashboard.php
    └── Profile.php
└── 📁public
    └── 📁_component
        └── app.css
        └── app.js
        └── app.php
    └── 📁_template
        └── Footer.php
        └── Header.php
    └── 403.php
    └── Login.php
└── 📁upload
└── .htaccess
└── index.php
└── LICENSE
└── README.md
```
```
AI

1. **Data Collection**: It queries a database to retrieve complaint records, grouped by status (Pending, Resolved, Closed) and month.

2. **Trend Analysis**: It organizes the complaint data chronologically and calculates trends for each status type.

3. **Prediction**: It uses a simple moving average algorithm to predict complaint volumes for the next 6 months.

4. **AI Integration**: It sends the historical data to Google Gemini API to get AI-powered insights and recommendations about the complaint trends.

5. **Visualization Preparation**: It structures all this data into a format suitable for the Highcharts visualization library that renders charts on the frontend.

The frontend displays:
- Summary cards showing counts of pending, resolved, and closed complaints
- A column chart showing complaint trends over time, including predictions
- AI-generated insights and recommendations for improving complaint handling

This system helps HR departments track complaint patterns, anticipate future volumes, and take proactive measures to address workplace issues.

```
