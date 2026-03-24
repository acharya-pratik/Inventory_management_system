# 🏪 Modern Inventory Management System

A lightweight, visually appealing, and feature-rich Inventory Management System built with PHP, MySQL, and Docker. This system is designed for small to medium-sized businesses to manage their stock, sales, and relationships with ease.

## 🚀 The "Easy Method" (Run on Any Device)

The fastest way to get this project running on any operating system (Windows, macOS, or Linux) is using **Docker**.

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running.

### Steps to Run:
1. **Clone the Repository:**
   ```bash
   git clone git@github.com:acharya-pratik/Inventory_management_system.git
   cd Inventory_management_system
   ```

2. **Start the Project:**
   Run the following command in your terminal:
   ```bash
   docker-compose up -d --build
   ```

3. **Access the Application:**
   - **Web App:** [http://localhost:8000](http://localhost:8000)
   - **Database Management (phpMyAdmin):** [http://localhost:8080](http://localhost:8080)
     - **Host:** `db`
     - **Username:** `root`
     - **Password:** `root`

---

## ✨ Key Features

- **📊 Smart Dashboard:** Real-time summary of total products, revenue, sales, and low-stock alerts.
- **🛒 Optimized Sales (V2):** A high-performance sales page with:
  - Real-time subtotal and grand total calculations.
  - Live stock validation (prevents over-selling).
  - Inline "Add New Customer" functionality.
- **📦 Inventory Management:** Full CRUD (Create, Read, Update, Delete) for Products, Categories, and Suppliers.
- **👥 Customer Management:** Track your customers and their purchase history.
- **📈 Detailed Reporting:** View daily revenue, best-selling products, and revenue by category.
- **🧾 Professional Invoices:** Generate and print detailed invoices for every transaction.
- **🔔 Stock Alerts:** Dedicated "Low Stock" page to help you restock before items run out.

## 🛠️ Technology Stack
- **Backend:** PHP 8.2 (PDO for secure database interactions)
- **Database:** MySQL 8
- **Frontend:** Vanilla CSS (Modern & Responsive UI), JavaScript (AJAX/ES6)
- **Deployment:** Docker & Docker Compose

## 📁 Project Structure
- `/sql`: Automatic database initialization scripts.
- `/includes`: Database connection and configuration.
- `/`: Core application logic and UI.

---
*Created with ❤️ for efficient inventory management.*
