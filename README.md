![Hotel System Logo](logo.png)
# 🏨 Simple Hotel Management System

## 📌 Overview

The **Simple Hotel Management System** is a web-based platform designed to streamline hotel operations, including user authentication, room management, client reservations, and payment processing. The system supports different roles such as Admin, Manager, Receptionist, and Client, each with distinct functionalities to ensure efficient hotel management.

## 🛠️ Technologies Used

- **Backend:** Laravel (PHP Framework)
- **Frontend:** Vue.js with Inertia.js
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Payment Gateway:** Stripe
- **UI Components:** Tailwind CSS, ShadCN/UI
- **Job Scheduling & Notifications:** Laravel Queues & Notifications

## ✨ Features

### **👑 Admin & Manager Functionalities**

- **🔑 Authentication System:**
    - User login and registration with role-based authentication
    - Forgot password functionality
- **📋 Manage Managers & Receptionists:**
    - Create, edit, and delete managers and receptionists
    - Assign roles and permissions
    - Ban/unban receptionists
- **🏢 Manage Floors & Rooms:**
    - Create, edit, and delete floors with unique floor numbers
    - Add and manage rooms with details (number, capacity, price)
    - Prevent deletion of reserved rooms
- **🧑‍💼 Client Management:**
    - View and manage all clients
    - Approve or reject new client registrations

### **🛎️ Receptionist Functionalities**

- **✅ Client Approval System:**
    - View pending clients and approve them
- **📅 Manage Client Reservations:**
    - View reservations made by clients
    - Display reservation details (room number, accompanying guests, paid price)

### **👤 Client Functionalities**

- **📝 Client Registration:**
    - Sign up with personal details (name, email, password, country, gender, etc.)
    - Status remains pending until approved by a receptionist
- **🏨 Make Reservations:**
    - View available rooms based on capacity
    - Make reservations with accompanying guests
    - Ensure guest number does not exceed room capacity
- **💳 Payments via Stripe:**
    - Pay for reservations securely using Stripe
- **📄 View My Reservations:**
    - See all past and active reservations along with payment details

## 🚀 How to Run the Project

1. Clone the repository:
    
    ```
    git clone https://github.com/your-repo/hotel-system.git
    cd hotel-system
    
    ```
    
2. Install dependencies:
    
    ```
    composer install
    npm install && npm run dev
    
    ```
    
3. Set up the environment file:
    
    ```
    cp .env.example .env
    
    ```
    
    - Configure database credentials
    - Set up Stripe API keys
4. Run database migrations and seed data:
    
    ```
    php artisan migrate --seed
    
    ```
    
5. Start the application:
    
    ```
    php artisan serve
    
    

---

This system ensures a smooth workflow for hotel management, offering role-based access and easy client interaction. 🚀
