# 🎓 GigEx — Student Freelance & Gig Exchange

> A full-stack web marketplace built for university students to showcase skills, offer freelance services, and manage campus gig projects.



## 👨‍💻 Group Information

* **Group Name:** [Insert Your Group Name / Number here]
* **Repository Link:** [Insert GitHub Repo URL]

### 👥 Team Members & Modules

| Student Name | Student ID | Assigned Module & Core Scope |
| :--- | :--- | :--- |
| **Parisa Asaf** | [23101270] | **Module 1:** Seller Management (Gig CRUD, Archiving, Dashboard UI) |
| **Maliha Sazzad** | [22201138] | **Module 2:** Marketplace & Discovery (Feed, Search, Filters, Profiles) |
| **Imamul Hossain Mahi** | [22221154] | **Module 3:** Hiring & Order Management (Hire Requests, Order Lifecycle) |
| **Fahim Hasan** | [22221160] | **Module 4:** Delivery & Feedback (Deliverables, Revisions, Reviews & Ratings) |

---

## 📌 Project Overview

**GigEx** is a peer-to-peer student marketplace designed to connect student freelancers with campus buyers needing specific micro-services (e.g., tutoring, graphic design, web development, content creation). 

### Key Features Across Modules:
1. **Seller Dashboard & Listing Management:** Full CRUD operations for gig services, media uploads, and non-destructive status-based archiving.
2. **Marketplace Discovery:** Dynamic public feed with real-time keyword search, category filtering, and price sorting.
3. **Hire Request & Order System:** Request submission workflows, status tracking (In Progress, Review), and order management.
4. **Deliverables & Reviews:** File delivery submission, revision requests, star rating system, and text reviews.
5. **Seller Analytics:** Active/completed order metrics, completion rate, six-month revenue, total earnings, and downloadable CSV summaries.
6. **Service Packages & Add-ons:** Optional Basic/Standard/Premium packages and paid extras with pricing snapshots carried into hire requests and orders.

### Added seller workflow

1. Run php artisan migrate.
2. Sign in as a seller and open **My Seller Dashboard** to view financial analytics or download the earnings CSV.
3. Create or edit a gig to configure service packages and up to 10 paid add-ons.
4. A buyer selects a package and add-ons on the hire-request form; the calculated quote becomes the accepted order price.
5. After a completed order, the buyer selects a rating with the clickable 1–5 star interface.

---

## 🛠️ Technical Architecture & Tech Stack

* **Framework:** Laravel 12
* **Language:** PHP 8.2+ / Blade Templating
* **Database:** MySQL
* **Frontend:** Bootstrap 5 / Custom CSS
* **Version Control:** Git / GitHub (`main` branch workflow)
