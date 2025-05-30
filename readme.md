# Japaneeds you 

**Japaneeds you** is my first website project ! Prepare your trip with all the informations you need or make a reservation with a guide to make your journey unforgettable.
"The website is available in French only. An English version is coming soon."

---

## What can you do ?

### User

- Informations pages to help you prepare your trip.( Interactive map, live weather, information pages... )
- After registration, earn 20 coins to make a reservation.( all your reservations history appears in your account)
- Receive an email at each step of the reservation process.
- After your reservation, you can leave a review for your guide.
- Users can register as guide.

### Guide

- To become a guide, users need to fill in their description, languages, specialties, and location.
- When creating a reservation, the guide provides the day, time, location, and other important details
- The guide can view the reviews left by users directly on their account.
- The guide receives their credits after the user confirms the end of the reservation, and 2 credits are allocated to the platform.

### Administrator

- The administrator can add new cities and view the list of existing ones.
- User and employee lists are accessible from the admin dashboard.
- New employee accounts can be created by the administrator.
- The admin dashboard includes statistics on reservations by day and accumulated credits.
- He can manage user reviews.

### Staff
- Can manage user reviews.

---

## Technologies Used

### Front-end

- **HTML**
- **CSS / SCSS**
- **Javascript (vanilla)**

### Back-end

- **PHP**
- **Symfony**
- **SQL (MariaDB)**
- **NoSQL (MongoDB)**

### Tools

- **Docker**(  to containerize the application, making it easy to deploy and run in any environment. )

---

## Work in Progress
- English version of the website
- Style/design improvements
- Guide receives a notification after a reservation

---

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Thomas2709sk/japaneeds.git
   cd japaneeds
   ```

2. **Using Docker**
   - Make sure you have [Docker](https://www.docker.com/products/docker-desktop) installed.
   - Start the containers:
     ```bash
     docker-compose up --build -d
     ```


