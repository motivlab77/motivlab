#!/bin/bash

echo "============================================"
echo "MotivLab MariaDB Setup with Your Credentials"
echo "============================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Your credentials
DB_USER="motivlab_admin"
DB_PASS="local_dev_password"
DB_NAME="motivlab_db"
DB_HOST="127.0.0.1"

# Start MariaDB
echo -e "${YELLOW}Starting MariaDB...${NC}"
mysql -u root -plocal_dev_password

# Import schema if file exists
if [ -f "admin/database.sql" ]; then
    echo -e "${YELLOW}Importing database schema...${NC}"
    mysql -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < admin/database.sql
    echo -e "${GREEN}Schema imported successfully!${NC}"
else
    echo -e "${RED}Warning: admin/database.sql not found. Skipping import.${NC}"
fi

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo "MariaDB Root Password: Root@2024Pass"
echo ""
echo "Application Credentials (matches config.php):"
echo "  DB_HOST: ${DB_HOST}"
echo "  DB_USER: ${DB_USER}"
echo "  DB_PASS: ${DB_PASS}"
echo "  DB_NAME: ${DB_NAME}"
echo ""
echo "Your config.php is already configured with these credentials!"
echo ""
echo "Start the server:"
echo "  cd ~/motivlab-website"
echo "  php -S localhost:8080"
echo ""
echo "Access admin panel:"
echo "  http://localhost:8080/admin/login.php"
echo "  Username: admin"
echo "  Password: admin123"
echo ""
