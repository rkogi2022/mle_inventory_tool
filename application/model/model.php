<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Model
{
    private $db;
    private $validCategories = ['Laptop', 'Smart Phone', 'Monitor', 'Mouse', 'Printer', 'CPU'];
    private $validTicketCategories = ['hardware', 'training', 'other'];
    private $validPriorities = ['low', 'medium', 'high', 'critical'];
    private $validStatuses = ['open', 'ongoing', 'resolved', 'closed'];

    public function __construct($db)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
        } else {
            die('Invalid database connection.');
        }
        
    }

  /** ---------------- Login and User management Functions-------------------- **/

    // login function
    
    public function getStaff($email) {
        $sql = "SELECT email, password, role, department, position FROM staff_login WHERE email = :email LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
    
        return $query->fetch(PDO::FETCH_OBJ);  
    }
    
    public function reset_password($email, $hashed_password,){
        $sql = "UPDATE staff_login SET password = :password WHERE email = :email";
        $query = $this->db->prepare($sql);
        $query->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        $query->bindValue(':email', $email, PDO::PARAM_STR);
        return $query->execute();
    }
    // user management function
    public function get_users()
    {
        $sql = "SELECT sl.*, d.department_name, p.position_name, CONCAT(loc.location_name, ' - ', o.office_name) as dutystation
                FROM staff_login sl
                LEFT JOIN departments d ON sl.department = d.id
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN offices o ON sl.dutystation = o.id
                LEFT JOIN locations loc ON o.location_id = loc.id";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
        
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function insert_user($email, $department, $position, $role, $dutystation = null, $password = 'mle2025')
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "INSERT INTO staff_login (email, department, position, role, dutystation, password) 
                VALUES (:email, :department, :position, :role, :dutystation, :password)";
        $query = $this->db->prepare($sql);
        $query->bindValue(':email', $email, PDO::PARAM_STR);
        $query->bindValue(':department', $department ?: null, PDO::PARAM_INT);
        $query->bindValue(':position', $position ?: null, PDO::PARAM_INT);
        $query->bindValue(':role', $role, PDO::PARAM_STR);
        $query->bindValue(':dutystation', $dutystation ?: null, PDO::PARAM_INT);
        $query->bindValue(':password', $hashed_password, PDO::PARAM_STR);

        return $query->execute();
    }
       
    public function edit_user($id, $email, $department, $position, $role, $dutystation)
    {
        $sql = "UPDATE staff_login 
                SET email=:email, department=:department, position=:position, role=:role, dutystation=:dutystation 
                WHERE id=:id";
        $query = $this->db->prepare($sql);
        $parameters = array(
            ':email' => $email,
            ':department' => $department,
            ':position' => $position,
            ':role' => $role,
            ':dutystation' => $dutystation,
            ':id' => $id
        );
        return $query->execute($parameters);
    }
         
    public function delete_user($id)
    {
        $sql = "DELETE FROM staff_login WHERE id=:id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);

        return $query->execute($parameters);
    }
    // Fetch departments
    public function get_departments()
    {
        $stmt = $this->db->prepare("SELECT id, department_name FROM departments");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Fetch positions
    public function get_positions()
    {
        $stmt = $this->db->prepare("SELECT id, position_name FROM positions");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Fetch roles
    public function get_roles()
    {
        return ['super_admin', 'admin', 'staff']; // Static ENUM values
    }


       //location model
    // Fetch all locations
    public function getLocations() {
        $sql = "SELECT * FROM locations ORDER BY created_at DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } 

    // Add new location
    public function addLocation($location_name) {
        $sql = "INSERT INTO locations (location_name) VALUES (:location_name)";
        $query = $this->db->prepare($sql);
        $parameters = array(':location_name' => $location_name);
        return $query->execute($parameters);
    }

    // Get a single location by ID
    public function getLocationById($id) {
        $sql = "SELECT * FROM locations WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        $query->execute($parameters);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Update location
    public function updateLocation($id, $location_name) {
        $sql = "UPDATE locations SET location_name = :location_name WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':location_name' => $location_name, ':id' => $id);
        return $query->execute($parameters);
    }

    // Delete location
    public function deleteLocation($id) {
        $sql = "DELETE FROM locations WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        return $query->execute($parameters);
    }

    
    //office model
    // Fetch all offices with their locations
    public function getOffices() {
        $sql = "SELECT offices.*, locations.location_name 
                FROM offices 
                JOIN locations ON offices.location_id = locations.id 
                ORDER BY office_name ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add new office
    public function addOffice($office_name, $location_id) {
        $sql = "INSERT INTO offices (office_name, location_id) 
            VALUES (:office_name, :location_id)";
        $query = $this->db->prepare($sql);
        $parameters = array(
            ':office_name' => $office_name,
            ':location_id' => $location_id
        );
        return $query->execute($parameters);
    }

    // Get a single office by ID
    public function getOfficeById($id) {
        $sql = "SELECT offices.*, locations.location_name 
                FROM offices 
                JOIN locations ON offices.location_id = locations.id 
                WHERE offices.id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        $query->execute($parameters);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Update office
    public function updateOffice($id, $office_name, $location_id) {
        $sql = "UPDATE offices SET office_name = :office_name, location_id = :location_id WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(
            ':office_name' => $office_name,
            ':location_id' => $location_id,
            ':id' => $id
        );
        return $query->execute($parameters);
    }

    // Delete office
    public function deleteOffice($id) {
        $sql = "DELETE FROM offices WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        return $query->execute($parameters);
    }

        //CATEGORY MODEL:
    // Fetch all categories
    public function getCategories() {
        $sql = "SELECT * FROM categories ORDER BY created_at DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add new category
    public function addCategory($category_name, $description) {
        if (!in_array($category_name, $this->validCategories)) {
            throw new InvalidArgumentException("Invalid category name.");
        }

        $sql = "INSERT INTO categories (category, description) VALUES (:category, :description)";
        $query = $this->db->prepare($sql);
        $parameters = array(':category' => $category_name, ':description' => $description);
        return $query->execute($parameters);
    }

    // Get a single category by ID
    public function getCategoryById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        $query->execute($parameters);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    //Get a single category byname
    public function getCategoryIdByName($category_name)
    {
        $sql = "SELECT id FROM categories WHERE category = :category";
        $query = $this->db->prepare($sql);
        $query->bindParam(':category', $category_name, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
    
        return $result ? $result['id'] : null;
    }
    
    // Update category
    public function updateCategory($id, $category_name, $description) {
        if (!in_array($category_name, $this->validCategories)) {
            throw new InvalidArgumentException("Invalid category name.");
        }

        $sql = "UPDATE categories SET category = :category, description = :description WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':category' => $category_name, ':description' => $description, ':id' => $id);
        return $query->execute($parameters);
    }

    // Delete category
    public function deleteCategory($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        return $query->execute($parameters);
    }

        // INVENTORY MODEL 
    // Fetch all inventory items
    public function getItems() {
        $sql = "SELECT i.id, c.category AS category, i.description, i.serial_number, 
                        i.tag_number, i.acquisition_date, i.acquisition_cost, i.warranty_date 
                FROM inventory i
                JOIN categories c ON i.category_id = c.id
                ORDER BY i.created_at DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public function searchItems(string $search)
    {
        $sql = "SELECT i.id, c.category AS category, i.description, i.serial_number, 
                    i.tag_number, i.acquisition_date, i.acquisition_cost, i.warranty_date 
                FROM inventory i
                JOIN categories c ON i.category_id = c.id
                WHERE i.description LIKE :search 
                OR i.tag_number LIKE :search
                OR i.serial_number LIKE :search
                ORDER BY i.created_at DESC";

        $query = $this->db->prepare($sql);
        $searchTerm = '%' . $search . '%';
        $query->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    //utility function to get location from staff_login table
    public function getCustodianLocation($custodian_id) {
        $sql = "SELECT dutystation FROM staff_login WHERE id = :id AND role = 'admin'";
        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $custodian_id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['dutystation'] : null;
    }

    // Add new inventory item
    public function addItem($category_id, $description, $serial_number, $tag_number, $acquisition_date, $acquisition_cost, $warranty_date, $custodian_id) {
        $location = $this->getCustodianLocation($custodian_id);

        $sql = "INSERT INTO inventory (
                    category_id,
                    description,
                    serial_number,
                    tag_number,
                    acquisition_date,
                    acquisition_cost,
                    warranty_date,
                    location,
                    custodian
                ) VALUES (
                    :category_id,
                    :description,
                    :serial_number,
                    :tag_number,
                    :acquisition_date,
                    :acquisition_cost,
                    :warranty_date,
                    :location,
                    :custodian
                )";

        $query = $this->db->prepare($sql);

        $query->bindValue(':category_id', $category_id);
        $query->bindValue(':description', $description);
        $query->bindValue(':serial_number', $serial_number);
        $query->bindValue(':tag_number', $tag_number ?: null, $tag_number ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $query->bindValue(':acquisition_date', $acquisition_date);
        $query->bindValue(':acquisition_cost', $acquisition_cost);
        $query->bindValue(':warranty_date', $warranty_date ?: null, $warranty_date ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $query->bindValue(':location', $location);
        $query->bindValue(':custodian', $custodian_id);

        return $query->execute();
    }

    // Bulk update method
    public function bulkInsertItems($items) {
        $sql = "INSERT INTO inventory (
                    category_id,
                    description,
                    serial_number,
                    tag_number,
                    acquisition_date,
                    acquisition_cost,
                    warranty_date,
                    location,
                    custodian,
                    created_at
                ) VALUES (
                    :category_id,
                    :description,
                    :serial_number,
                    :tag_number,
                    :acquisition_date,
                    :acquisition_cost,
                    :warranty_date,
                    :location,
                    :custodian,
                    NOW()
                )";

        $query = $this->db->prepare($sql);

        foreach ($items as $item) {
            $location = $this->getCustodianLocation($item['custodian']);

            $query->bindValue(':category_id', $item['category_id'], PDO::PARAM_INT);
            $query->bindValue(':description', $item['description'], PDO::PARAM_STR);
            $query->bindValue(':serial_number', $item['serial_number'], PDO::PARAM_STR);
            $query->bindValue(':tag_number', $item['tag_number'] ?: null, $item['tag_number'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $query->bindValue(':acquisition_date', $item['acquisition_date'], PDO::PARAM_STR);
            $query->bindValue(':acquisition_cost', $item['acquisition_cost'], PDO::PARAM_STR);
            $query->bindValue(':warranty_date', $item['warranty_date'] ?: null, $item['warranty_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $query->bindValue(':location', $location, PDO::PARAM_STR);
            $query->bindValue(':custodian', $item['custodian'], PDO::PARAM_STR);

            $query->execute();
        }

        return true;
    }

    //checking duplicates in bulk upload
    public function isSerialNumberExists($serial_number)
    {
        $sql = "SELECT COUNT(*) FROM inventory WHERE serial_number = :serial_number";
        $query = $this->db->prepare($sql);
        $query->execute([':serial_number' => $serial_number]);
        return $query->fetchColumn() > 0;
    }
    //export inventorylist
    public function getAllInventoryItems() 
    {
        $sql = "SELECT 
                    category_id,
                    description,
                    serial_number,
                    tag_number,
                    acquisition_date,
                    acquisition_cost,
                    warranty_date,
                    location,
                    custodian,
                    created_at
                FROM inventory";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        ///reupload edited sheet
    public function bulkInsertFromExport($items) 
    {
        $sql = "INSERT INTO inventory (
                    category_id,
                    description,
                    serial_number,
                    tag_number,
                    acquisition_date,
                    acquisition_cost,
                    warranty_date,
                    location,
                    custodian,
                    created_at
                ) VALUES (
                    :category_id,
                    :description,
                    :serial_number,
                    :tag_number,
                    :acquisition_date,
                    :acquisition_cost,
                    :warranty_date,
                    :location,
                    :custodian,
                    :created_at
                )";

        $stmt = $this->db->prepare($sql);

        foreach ($items as $item) {
            $stmt->bindValue(':category_id', $item['category_id'], PDO::PARAM_INT);
            $stmt->bindValue(':description', $item['description'], PDO::PARAM_STR);
            $stmt->bindValue(':serial_number', $item['serial_number'], PDO::PARAM_STR);
            $stmt->bindValue(':tag_number', $item['tag_number'] ?: null, $item['tag_number'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':acquisition_date', $item['acquisition_date'], PDO::PARAM_STR);
            $stmt->bindValue(':acquisition_cost', $item['acquisition_cost'], PDO::PARAM_STR);
            $stmt->bindValue(':warranty_date', $item['warranty_date'] ?: null, $item['warranty_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':location', $item['location'], PDO::PARAM_INT);
            $stmt->bindValue(':custodian', $item['custodian'], PDO::PARAM_INT);
            $stmt->bindValue(':created_at', $item['created_at'], PDO::PARAM_STR);

            $stmt->execute();
        }

        return true;
    }

    public function updateItemBySerialNumber($serial_number, $item) 
    {
        $sql = "UPDATE inventory SET
                    category_id = :category_id,
                    description = :description,
                    tag_number = :tag_number,
                    acquisition_date = :acquisition_date,
                    acquisition_cost = :acquisition_cost,
                    warranty_date = :warranty_date,
                    location = :location,
                    custodian = :custodian,
                    created_at = :created_at
                WHERE serial_number = :serial_number";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $item['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':description', $item['description'], PDO::PARAM_STR);
        $stmt->bindValue(':tag_number', $item['tag_number'] ?: null, $item['tag_number'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':acquisition_date', $item['acquisition_date'], PDO::PARAM_STR);
        $stmt->bindValue(':acquisition_cost', $item['acquisition_cost'], PDO::PARAM_STR);
        $stmt->bindValue(':warranty_date', $item['warranty_date'] ?: null, $item['warranty_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':location', $item['location'], PDO::PARAM_INT);
        $stmt->bindValue(':custodian', $item['custodian'], PDO::PARAM_INT);
        $stmt->bindValue(':created_at', $item['created_at'], PDO::PARAM_STR);
        $stmt->bindValue(':serial_number', $serial_number, PDO::PARAM_STR);
        return $stmt->execute();
    }

    
    // Get a single inventory item by ID
    public function getItemById($id) {
        $sql = "SELECT * FROM inventory WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        $query->execute($parameters);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Update inventory item
    public function updateItem($id,$category_id,$description,$serial_number,$tag_number,$acquisition_date,$acquisition_cost,$warranty_date,$custodian) 
    {
        $location = $this->getCustodianLocation($custodian);
        if (!$location) {
            $location = 'Unknown';
        }

        $sql = "UPDATE inventory SET
                    category_id = :category_id,
                    description = :description,
                    serial_number = :serial_number,
                    tag_number = :tag_number,
                    acquisition_date = :acquisition_date,
                    acquisition_cost = :acquisition_cost,
                    warranty_date = :warranty_date,
                    location = :location,
                    custodian = :custodian
                WHERE id = :id";

        $query = $this->db->prepare($sql);

        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $query->bindValue(':description', $description, PDO::PARAM_STR);
        $query->bindValue(':serial_number', $serial_number, PDO::PARAM_STR);

        // ✅ Fix tag_number binding
        if (!empty($tag_number)) {
            $query->bindValue(':tag_number', $tag_number, PDO::PARAM_STR);
        } else {
            $query->bindValue(':tag_number', null, PDO::PARAM_NULL);
        }

        $query->bindValue(':acquisition_date', $acquisition_date, PDO::PARAM_STR);
        $query->bindValue(':acquisition_cost', $acquisition_cost);

        // ✅ Fix warranty_date binding
        if (!empty($warranty_date)) {
            $query->bindValue(':warranty_date', $warranty_date, PDO::PARAM_STR);
        } else {
            $query->bindValue(':warranty_date', null, PDO::PARAM_NULL);
        }

        $query->bindValue(':location', $location, PDO::PARAM_STR);
        $query->bindValue(':custodian', $custodian, PDO::PARAM_INT);

        return $query->execute();
    }

    // Delete inventory item
    public function deleteItem($id) {
        $sql = "DELETE FROM inventory WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        return $query->execute($parameters);
    }

            //inventory assgnment model
    // Fetch all assignments
    public function getAllAssignments() {
        $sql = "SELECT 
                    ia.id,
                    CONCAT(
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 2)),
                        ' ',
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 2))
                    ) AS user_name,
                    ia.email,
                    d.department_name AS department,  
                    p.position_name AS position,    
                    CONCAT(loc.location_name, ' - ', o.office_name) AS location, -- formatted location
                    i.category_id,
                    i.description,
                    ia.serial_number,
                    ia.tag_number,
                    ia.date_assigned,
                    ia.managed_by,
                    ia.acknowledgment_status,
                    ia.confirmed,          
                    ia.confirmation_date,
                    ia.created_at,
                    ia.updated_at
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN staff_login sl ON ia.email = sl.email
                LEFT JOIN departments d ON sl.department = d.id  
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN offices o ON sl.dutystation = o.id 
                LEFT JOIN locations loc ON o.location_id = loc.id 
                LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id  
                WHERE ir.assignment_id IS NULL";  
    
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
      
      
    // Fetch all unassigned items (not pending or approved)
    public function getUnassignedInventory()
    {
        $sql = "SELECT 
                    i.id, 
                    c.category AS category,  
                    i.description, 
                    i.serial_number, 
                    i.tag_number,
                    i.custodian,
                    sl.email AS custodian_email
                FROM inventory i
                LEFT JOIN categories c ON i.category_id = c.id
                LEFT JOIN staff_login sl ON i.custodian = sl.id
                WHERE 
                    -- Ensure item is not currently assigned
                    i.id NOT IN (
                        SELECT ia.item FROM inventory_assignment ia
                        WHERE ia.acknowledgment_status IN ('pending', 'approved', 'acknowledged')
                    )
                    -- Ensure item is either never assigned OR returned and marked as functional OR is repairable
                    AND (
                        -- Exclude lost items
                        i.id NOT IN (
                            SELECT ia.item FROM inventory_assignment ia
                            JOIN inventory_returned ir ON ia.id = ir.assignment_id
                            WHERE ir.item_state = 'lost' 
                        )
                        -- Include approved functional items
                        OR i.id IN (
                            SELECT ia.item FROM inventory_assignment ia
                            JOIN inventory_returned ir ON ia.id = ir.assignment_id
                            WHERE ir.item_state = 'functional'
                            AND ir.status = 'approved' 
                        )
                        -- Include repairable damaged items
                        OR i.id IN (
                            SELECT ia.item FROM inventory_assignment ia
                            JOIN inventory_returned ir ON ia.id = ir.assignment_id
                            WHERE ir.item_state = 'damaged'
                            AND ir.repair_status = 'Repairable' 
                        )
                    )";

        $query = $this->db->prepare($sql);
        $query->execute();
        $items = $query->fetchAll(PDO::FETCH_ASSOC);

        // Parse custodian name from email with first letters uppercase
        foreach ($items as &$item) {
            if (!empty($item['custodian_email'])) {
                $namePart = explode('@', $item['custodian_email'])[0]; // get part before @
                $item['custodian_name'] = $this->toPascalCase($namePart);
            } else {
                $item['custodian_name'] = 'Unassigned';
            }
        }

        return $items;
    }

    // Helper function to convert string to PascalCase (First letter of each word uppercase, no spaces)
    private function toPascalCase($string)
    {
        $string = str_replace(['-', '_', '.'], ' ', strtolower($string));
        $words = explode(' ', $string);
        $pascalCased = '';
        foreach ($words as $word) {
            $pascalCased .= ucfirst($word); // capitalize first letter of each word
        }
        return $pascalCased;
    }
     
    // Fetch all users
    public function getAllUsers()
    {
        $sql = "SELECT id, email FROM staff_login";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user details by ID
    public function getUserById($user_id)
    {
        $sql = "SELECT email FROM staff_login WHERE id = :user_id";
        $query = $this->db->prepare($sql);
        $query->execute([':user_id' => $user_id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Get manager's email from staff_login (for managed_by field)
    public function getManagerEmail($email)
    {
        $sql = "SELECT email FROM staff_login WHERE email = :email";
        $query = $this->db->prepare($sql);
        $query->execute([':email' => $email]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserProfileByEmail($email)
    {
        $sql = "SELECT 
                    sl.email,
                    d.department_name AS department,
                    p.position_name AS position
                FROM staff_login sl
                LEFT JOIN departments d ON sl.department = d.id
                LEFT JOIN positions p ON sl.position = p.id
                WHERE sl.email = :email
                LIMIT 1";

        $query = $this->db->prepare($sql);
        $query->execute([':email' => $email]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }


    public function getItemSummariesByIds(array $assignment_ids)
    {
        if (empty($assignment_ids)) {
            return [];
        }

        // Prepare placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($assignment_ids), '?'));

        // SQL: join inventory_assignment with inventory to get description
        $sql = "SELECT ia.serial_number, ia.tag_number, inv.description
                FROM inventory_assignment ia
                LEFT JOIN inventory inv ON ia.item = inv.id
                WHERE ia.id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($assignment_ids);

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Fetched items from DB in getItemSummariesByIds(): " . print_r($items, true));

        $summaries = [];
        foreach ($items as $item) {
            $serial = isset($item['serial_number']) && $item['serial_number'] !== '' ? $item['serial_number'] : 'N/A';
            $tag = isset($item['tag_number']) && $item['tag_number'] !== '' ? $item['tag_number'] : 'N/A';
            $desc = isset($item['description']) && $item['description'] !== '' ? $item['description'] : 'N/A';

            $summaries[] = "Description: {$desc}, Serial: {$serial}, Tag: {$tag}";
        }

        return $summaries;
    }


    // Assign items to users
public function addAssignment($user_id, $item_ids, $date_assigned, $manager_email)
{
    try {
        // 1️⃣ Get manager email
        $manager = $this->getManagerEmail($manager_email);
        if (!$manager) {
            return "Invalid manager email: " . htmlspecialchars($manager_email);
        }
        $managed_by = implode(' ', array_map(function ($part) {
            return ucfirst(strtolower($part));
        }, preg_split('/[._]/', strtok($manager['email'], '@'))));

        // 2️⃣ Get user details
        $userSql = "SELECT 
                        email,
                        CONCAT(COALESCE(department, 'N/A'), ' ', COALESCE(position, 'N/A')) AS role
                    FROM staff_login
                    WHERE id = :user_id";
        $userQuery = $this->db->prepare($userSql);
        $userQuery->execute([':user_id' => $user_id]);
        $user = $userQuery->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "User not found.";
        }

        $formattedUserName = implode(' ', array_map(function ($part) {
            return ucfirst(strtolower($part));
        }, preg_split('/[._]/', strtok($user['email'], '@'))));

        // 3️⃣ Initialize item list for email
        $itemList = [];

        // 4️⃣ Loop through items and assign
        foreach ($item_ids as $item_id) {
            // Fetch item details
            $itemSql = "SELECT description, serial_number, tag_number FROM inventory WHERE id = :item_id";
            $itemQuery = $this->db->prepare($itemSql);
            $itemQuery->execute([':item_id' => $item_id]);
            $item = $itemQuery->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return "Item with ID $item_id not found in inventory.";
            }

            // Check if item already assigned
            $checkSql = "SELECT COUNT(*) FROM inventory_assignment 
                         WHERE item = :item_id AND acknowledgment_status IN ('pending', 'approved')";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute([':item_id' => $item_id]);
            if ($checkQuery->fetchColumn() > 0) {
                return "Item with ID $item_id is already assigned.";
            }

            // Insert assignment record
            $parameters = [
                ':name' => $formattedUserName,
                ':email' => $user['email'],
                ':role' => $user['role'],
                ':item_id' => $item_id,
                ':serial_number' => $item['serial_number'],
                ':tag_number' => $item['tag_number'],
                ':managed_by' => $managed_by,
                ':date_assigned' => $date_assigned
            ];

            $sql = "INSERT INTO inventory_assignment 
                        (name, email, role, item, serial_number, tag_number, managed_by, acknowledgment_status, created_at, updated_at, date_assigned)
                    VALUES 
                        (:name, :email, :role, :item_id, :serial_number, :tag_number, :managed_by, 'pending', NOW(), NOW(), :date_assigned)";
            
            $query = $this->db->prepare($sql);
            if (!$query->execute($parameters)) {
                $errorInfo = $query->errorInfo();
                return "Failed to assign item with ID $item_id. Error: " . $errorInfo[2];
            }

            // Add item to email list
            $itemList[] = "Description: {$item['description']}, Serial Number: {$item['serial_number']}, Tag Number: {$item['tag_number']}";
        }

        // 5️⃣ Send notification to manager
        $this->sendAssignmentNotificationToManager($manager_email, $managed_by, $formattedUserName, $itemList);

        return "Items successfully assigned and manager notified!";

    } catch (PDOException $e) {
        die("<strong>SQL Exception:</strong> " . $e->getMessage());
    } catch (Exception $e) {
        die("<strong>General Exception:</strong> " . $e->getMessage());
    }
}
    protected function sendAssignmentNotificationToManager($managerEmail, $managerName, $recipientName, $itemList)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');
            $mail->addAddress($managerEmail, $managerName);
            $mail->addBCC('information.systems@evidenceaction.org');

            $mail->isHTML(true);
            $mail->Subject = "Notification: {$recipientName} Assigned Inventory Item(s)";

            $itemListHtml = "<ul>";
            foreach ($itemList as $item) {
                $parts = explode(',', $item);
                $formattedParts = [];
                foreach ($parts as $part) {
                    $labelValue = explode(':', $part, 2);
                    if (count($labelValue) == 2) {
                        $label = htmlspecialchars(trim($labelValue[0]));
                        $value = htmlspecialchars(trim($labelValue[1]));
                        $formattedParts[] = "<strong>{$label}:</strong> {$value}";
                    } else {
                        $formattedParts[] = htmlspecialchars($part);
                    }
                }
                $itemListHtml .= "<li>" . implode(', ', $formattedParts) . "</li>";
            }
            $itemListHtml .= "</ul>";

            $mail->Body = "
                <p>Dear {$managerName},</p>

                <p>This is to notify you that your supervisee <strong>{$recipientName}</strong> has been assigned the following inventory item(s):</p>

                {$itemListHtml}

                <p>If you believe this assignment is incorrect, kindly contact the IT department immediately.</p>

                <p>Regards,<br>MLE Inventory Tool</p>
            ";

            $mail->AltBody = "{$recipientName} has been assigned inventory item(s).";

            $mail->CharSet = 'UTF-8';
            $mail->send();
            error_log("Manager notification sent to: {$managerEmail}");
        } catch (Exception $e) {
            error_log("PHPMailer Manager Error: " . $mail->ErrorInfo);
        }
    }

    // //add single assignment
    public function assignSingleItem($user_id, $item_id, $date_assigned, $manager_email)
    {
        $created = date('Y-m-d H:i:s');

        // 1. Validate Manager
        $manager = $this->getManagerEmail($manager_email);
        if (!$manager) {
            return ['type' => 'error', 'message' => "Invalid manager email: " . htmlspecialchars($manager_email)];
        }
        $managed_by = implode(' ', array_map('ucfirst', explode('.', strtok($manager['email'], '@'))));

        // 2. Get User Info
        $userSql = "SELECT email AS name, email, CONCAT(COALESCE(department, 'N/A'), ' ', COALESCE(position, 'N/A')) AS role FROM staff_login WHERE id = :user_id";
        $userQuery = $this->db->prepare($userSql);
        $userQuery->execute([':user_id' => $user_id]);
        $user = $userQuery->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ['type' => 'error', 'message' => "User not found."];
        }

        // 3. Get Item Info
        $itemSql = "SELECT serial_number, tag_number FROM inventory WHERE id = :item_id";
        $itemQuery = $this->db->prepare($itemSql);
        $itemQuery->execute([':item_id' => $item_id]);
        $item = $itemQuery->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            return ['type' => 'error', 'message' => "Item with ID $item_id not found in inventory."];
        }

        // 4. Get current assignment ID for this item (if any)
        $assignmentSql = "SELECT id FROM inventory_assignment WHERE item = :item_id AND acknowledgment_status IN ('pending', 'acknowledged') ORDER BY created_at DESC LIMIT 1";
        $assignmentQuery = $this->db->prepare($assignmentSql);
        $assignmentQuery->execute([':item_id' => $item_id]);
        $assignment = $assignmentQuery->fetch(PDO::FETCH_ASSOC);

        $assignment_id = $assignment['id'] ?? null;

        // 5. Check if item is lost or unrepairable damaged
        if ($assignment_id) {
            $returnSql = "SELECT item_state, repair_status 
                        FROM inventory_returned 
                        WHERE assignment_id = :assignment_id 
                        AND status = 'approved' 
                        ORDER BY approved_date DESC 
                        LIMIT 1";
            $returnQuery = $this->db->prepare($returnSql);
            $returnQuery->execute([':assignment_id' => $assignment_id]);
            $returnStatus = $returnQuery->fetch(PDO::FETCH_ASSOC);

            if ($returnStatus) {
                if ($returnStatus['item_state'] === 'lost') {
                    return ['type' => 'warning', 'message' => "Cannot assign. The item is marked as lost."];
                } elseif ($returnStatus['item_state'] === 'damaged' && $returnStatus['repair_status'] === 'Unrepairable') {
                    return ['type' => 'warning', 'message' => "Cannot assign. Item is marked as retired."];
                }
            }
        }

        // 6. Check if item is already assigned
        $checkSql = "SELECT COUNT(*) FROM inventory_assignment 
                    WHERE item = :item_id AND acknowledgment_status IN ('pending', 'acknowledged')";
        $checkQuery = $this->db->prepare($checkSql);
        $checkQuery->execute([':item_id' => $item_id]);
        if ($checkQuery->fetchColumn() > 0) {
            return ['type' => 'error', 'message' => "The item is already assigned."];
        }

        // 7. Insert Assignment
        try {
            $sql = "INSERT INTO inventory_assignment 
                        (name, email, role, item, serial_number, tag_number, managed_by, acknowledgment_status, created_at, updated_at, date_assigned)
                    VALUES 
                        (:name, :email, :role, :item_id, :serial_number, :tag_number, :managed_by, 'pending', NOW(), NOW(), :date_assigned)";
            $query = $this->db->prepare($sql);
            $parameters = [
                ':name' => $user['name'],
                ':email' => $user['email'],
                ':role' => $user['role'],
                ':item_id' => $item_id,
                ':serial_number' => $item['serial_number'],
                ':tag_number' => $item['tag_number'],
                ':managed_by' => $managed_by,
                ':date_assigned' => $date_assigned
            ];

            $success = $query->execute($parameters);
            if ($success) {
                return [
                    'type' => 'success',
                    'message' => "Item assigned successfully!",
                    'assignment_id' => $this->db->lastInsertId() 
                ];
            } else {
                $error = $query->errorInfo();
                return ['type' => 'error', 'message' => "Assignment failed: " . implode(" | ", $error)];
            }
        } catch (PDOException $e) {
            return ['type' => 'error', 'message' => "DB Error: " . $e->getMessage()];
        }
    }
 
    //get manageers
    public function getManagers()
    {
        $sql = "SELECT 
                    id,
                    email,
                    CONCAT(
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', 1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', 1), 2)),
                        ' ',
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', -1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', -1), 2))
                    ) AS name
                FROM staff_login";

        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    //get assignment by id
    public function getAssignmentById($assignment_id)
    {
        $sql = "SELECT ia.*, au.id AS user_id, au.email AS user_email, i.id AS item_id, i.description, i.serial_number
                FROM inventory_assignment ia
                JOIN staff_login au ON ia.email = au.email
                LEFT JOIN inventory i ON ia.item = i.id
                WHERE ia.id = :assignment_id";

        $query = $this->db->prepare($sql);
        $query->execute([':assignment_id' => $assignment_id]);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return null; // Assignment not found
        }

        $assignment = $result[0]; // Base assignment details

        // Collect items into an array
        $assignment['items'] = [];
        foreach ($result as $row) {
            if ($row['item_id']) {
                $assignment['items'][] = [
                    'id' => $row['item_id'],
                    'description' => $row['description'],
                    'serial_number' => $row['serial_number']
                ];
            }
        }

        return $assignment;
    }
    public function getLatestAssignmentIdByItem($itemId)
    {
        $sql = "SELECT id FROM inventory_assignment WHERE item = :item_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['item_id' => $itemId]);
        return $stmt->fetchColumn(); // Returns just the id
    }

    // Update assignment only if acknowledgment_status is pending
    public function updateAssignment($assignment_id, $updatedData, $inventory_ids)
        {
            // Fetch existing assignment to verify it exists
            $existingAssignmentSql = "SELECT email FROM inventory_assignment WHERE id = :id";
            $existingStmt = $this->db->prepare($existingAssignmentSql);
            $existingStmt->execute([':id' => $assignment_id]);
            $existingAssignment = $existingStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingAssignment) {
                return "Assignment not found.";
            }

            // Get user details by user_id
            $userSql = "SELECT 
                            email,
                            CONCAT(COALESCE(department, 'N/A'), ' ', COALESCE(position, 'N/A')) AS role
                        FROM staff_login
                        WHERE id = :user_id";

            $userQuery = $this->db->prepare($userSql);
            $userQuery->execute([':user_id' => $updatedData['user_id']]);
            $user = $userQuery->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return "User not found.";
            }

            // Format user name like in addAssignment
            $formattedUserName = implode(' ', array_map(function ($part) {
                return ucfirst(strtolower($part));
            }, preg_split('/[._]/', strtok($user['email'], '@'))));

            // Get manager email and format manager name
            $manager = $this->getManagerEmail($updatedData['managed_by']);
            if (!$manager) {
                return "Invalid manager email: " . htmlspecialchars($updatedData['managed_by']);
            }
            $managed_by = implode(' ', array_map(function ($part) {
                return ucfirst(strtolower($part));
            }, preg_split('/[._]/', strtok($manager['email'], '@'))));

            // Begin transaction to safely update assignment and items
            $this->db->beginTransaction();

            try {
                // Update assignment main details (name, email, role, managed_by, date_assigned)
                $updateSql = "UPDATE inventory_assignment
                            SET name = :name, email = :email, role = :role, managed_by = :managed_by, 
                                date_assigned = :date_assigned, updated_at = NOW()
                            WHERE id = :id";

                $updateStmt = $this->db->prepare($updateSql);
                $updateParams = [
                    ':id' => $assignment_id,
                    ':name' => $formattedUserName,
                    ':email' => $user['email'],
                    ':role' => $user['role'],
                    ':managed_by' => $managed_by,
                    ':date_assigned' => $updatedData['date_assigned']
                ];

                if (!$updateStmt->execute($updateParams)) {
                    $this->db->rollBack();
                    return "Failed to update assignment main details.";
                }

                $deleteSql = "DELETE FROM inventory_assignment WHERE id = :id";
                $deleteStmt = $this->db->prepare($deleteSql);
                $deleteStmt->execute([':id' => $assignment_id]);

                // Insert new inventory assignments (one per item)
                foreach ($inventory_ids as $item_id) {
                    // Get item details
                    $itemSql = "SELECT serial_number, tag_number FROM inventory WHERE id = :item_id";
                    $itemQuery = $this->db->prepare($itemSql);
                    $itemQuery->execute([':item_id' => $item_id]);
                    $item = $itemQuery->fetch(PDO::FETCH_ASSOC);

                    if (!$item) {
                        $this->db->rollBack();
                        return "Item with ID $item_id not found in inventory.";
                    }

                    $insertSql = "INSERT INTO inventory_assignment 
                                    (name, email, role, item, serial_number, tag_number, managed_by, 
                                    acknowledgment_status, created_at, updated_at, date_assigned) 
                                VALUES 
                                    (:name, :email, :role, :item_id, :serial_number, :tag_number, 
                                    :managed_by, 'pending', NOW(), NOW(), :date_assigned)";

                    $insertStmt = $this->db->prepare($insertSql);
                    $insertParams = [
                        ':name' => $formattedUserName,
                        ':email' => $user['email'],
                        ':role' => $user['role'],
                        ':item_id' => $item_id,
                        ':serial_number' => $item['serial_number'],
                        ':tag_number' => $item['tag_number'],
                        ':managed_by' => $managed_by,
                        ':date_assigned' => $updatedData['date_assigned']
                    ];

                    if (!$insertStmt->execute($insertParams)) {
                        $this->db->rollBack();
                        return "Failed to assign item with ID $item_id.";
                    }
                }

                $this->db->commit();
                return "Assignment successfully updated!";

            } catch (Exception $e) {
                $this->db->rollBack();
                return "Database error: " . $e->getMessage();
            }
        }

    
    // Delete assignment only if acknowledgment_status is pending
    public function deleteAssignment($id) {
        // Ensure only pending assignments can be deleted
        $assignment = $this->getAssignmentById($id);
        if (!$assignment || $assignment['acknowledgment_status'] !== 'pending') {
            return false;
        }

        $sql = "DELETE FROM inventory_assignment WHERE id = ?";
        $query = $this->db->prepare($sql);
        return $query->execute([$id]);
    }
    //marking item as received(pending to acknowledge)
    // Get pending assignments for the logged-in user
    public function getPendingAssignmentsByLoggedInUser($user_email)
    {
        $sql = "SELECT 
                    ia.id,
                                       
                    CONCAT(
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 2)),
                        ' ',
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 2))
                    ) AS user_name,
                    ia.email,
                    d.department_name AS department,  
                    p.position_name AS position,    
                    i.category_id,
                    i.description,
                    ia.serial_number,
                    ia.tag_number,
                    ia.date_assigned,
                    ia.managed_by,
                    ia.acknowledgment_status,
                    ia.created_at,
                    ia.updated_at
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN staff_login sl ON ia.email = sl.email
                LEFT JOIN departments d ON sl.department = d.id  
                LEFT JOIN positions p ON sl.position = p.id 
                WHERE ia.email = :user_email
                AND ia.acknowledgment_status = 'pending'";
    
        $query = $this->db->prepare($sql);
        $query->execute([':user_email' => $user_email]);
    
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
        // Acknowledge pending items
    public function acknowledgeAssignment($assignment_id, $user_email, $device_state)
    {
        $sql = "UPDATE inventory_assignment
                SET acknowledgment_status = 'acknowledged', 
                    device_state = :device_state,
                    updated_at = NOW()
                WHERE id = :assignment_id AND email = :user_email";

        $query = $this->db->prepare($sql);
        $query->execute([
            ':assignment_id' => $assignment_id,
            ':user_email' => $user_email,
            ':device_state' => $device_state
        ]);

        return $query->rowCount();
    }
        ///get items assigned to a logged in user
    public function getApprovedAssignmentsByLoggedInUser($user_email)
    {
        $sql = "SELECT 
                    ia.id,
                    ia.name AS user_name,
                    ia.email,
                    d.department_name AS department,  
                    p.position_name AS position,
                    i.category_id,
                    i.description,
                    ia.serial_number,
                    ia.tag_number,
                    ia.date_assigned,
                    ia.managed_by,
                    ia.acknowledgment_status,
                    ia.device_state,    
                    ia.reconfirm_enabled,  
                    ia.confirmed,          
                    ia.confirmation_date,
                    ia.created_at,
                    ia.updated_at
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN staff_login sl ON ia.email = sl.email
                LEFT JOIN departments d ON sl.department = d.id  
                LEFT JOIN positions p ON sl.position = p.id 
                WHERE ia.email = :user_email
                AND ia.acknowledgment_status = 'acknowledged'
                -- Exclude items that have been returned and approved
                AND ia.id NOT IN (
                    SELECT ir.assignment_id
                    FROM inventory_returned ir
                    WHERE ir.status = 'approved' 
                    AND ir.return_date IS NOT NULL
                )";
        
        $query = $this->db->prepare($sql);
        $query->execute([':user_email' => $user_email]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    //automated email notifications sent to users who have pending assignments that are 30 days old that recurs in 30 day intervals
    public function getUsersWithPendingAcknowledgment($limit = 10, $offset = 0)
    {
        $sql = "SELECT DISTINCT email 
                FROM inventory_assignment 
                WHERE acknowledgment_status = 'pending' 
                AND date_assigned <= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND (
                    last_reminder_sent_at IS NULL 
                    OR last_reminder_sent_at <= DATE_SUB(NOW(), INTERVAL 30 DAY)
                )
                LIMIT :limit OFFSET :offset";
        
        $query = $this->db->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsersWithPendingAcknowledgment()
    {
        $sql = "SELECT DISTINCT email
                FROM inventory_assignment
                WHERE acknowledgment_status = 'pending'
                AND email IS NOT NULL";

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    // Add method to update reminder tracking
    public function updateReminderTracking($email)
    {
        $sql = "UPDATE inventory_assignment
                SET last_reminder_sent_at = NOW(),
                    reminder_count = COALESCE(reminder_count,0) + 1
                WHERE email = :email
                AND acknowledgment_status = 'pending'";

        $query = $this->db->prepare($sql);
        $query->bindValue(':email', $email, PDO::PARAM_STR);

        return $query->execute();
    }

            //item returning process...
        //model to show returned item
    public function getReturnedItems($returned_by)
        {
            try {
                $sql = "SELECT ir.id, 
                                i.description, 
                                i.serial_number, 
                                i.tag_number,
                                CONCAT(
                                    UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 1)),
                                    LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 2)),
                                    ' ',
                                    UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 1)),
                                    LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 2))
                                ) AS returned_by_name, 
                                ir.return_date, 
                                ir.status, 
                                ir.item_state, 
                                ir.approved_by, 
                                ir.approved_date,
                                ir.repair_status, 
                                ir.disapproval_comment, 
                                CONCAT(
                                    UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', 1), 1)),
                                    LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', 1), 2)),
                                    ' ',
                                    UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', -1), 1)),
                                    LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', -1), 2))
                                ) AS receiver_name,
                                ir.created_at, 
                                ir.updated_at
                        FROM inventory_returned ir
                        INNER JOIN inventory_assignment ia ON ir.assignment_id = ia.id  
                        INNER JOIN inventory i ON ia.item = i.id 
                        INNER JOIN staff_login sl ON ir.returned_by = sl.email
                        INNER JOIN staff_login sl_receiver ON ir.receiver_id = sl_receiver.id  
                        WHERE ir.returned_by = :returned_by
                        ORDER BY ir.return_date ASC";  // Sort by return_date ascending
                
                $query = $this->db->prepare($sql);
                $query->execute([ ':returned_by' => $returned_by ]);
        
                $returnedItems = $query->fetchAll(PDO::FETCH_ASSOC);
        
                return $returnedItems;
            } catch (PDOException $e) {
                die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
            }
        }
        
           //delete returned items that are not approve
    public function deleteReturn($id)
           {
               try {
                   // Check item status first
                   $sql = "SELECT status FROM inventory_returned WHERE id = :id";
                   $query = $this->db->prepare($sql);
                   $query->execute([":id" => $id]);
                   $item = $query->fetch(PDO::FETCH_ASSOC);
           
                   if (!$item) {
                       return false; // Item not found
                   }
           
                   if (strtolower($item['status']) !== 'pending') {
                       return false; // Only pending items can be deleted
                   }
           
                   // Delete the item
                   $sql = "DELETE FROM inventory_returned WHERE id = :id";
                   $query = $this->db->prepare($sql);
                   return $query->execute([":id" => $id]); // Return true if deleted
           
               } catch (PDOException $e) {
                   die("SQL Error: " . $e->getMessage());
               }
           }
                 
    // Get a single returned item by ID
    public function getReturnedItemById($id)
    {
        $sql = "SELECT * FROM inventory_returned WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        $query->execute($parameters);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getReceivers()
    {
        $sql = "SELECT 
                    id, 
                    CONCAT(
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', 1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', 1), 2)),
                        ' ',
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', -1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', -1), 2))
                    ) AS name
                FROM staff_login
                WHERE role = 'admin';"; 
        
        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    //returning items(original)
    // public function recordReturn($assignment_id, $returned_by_email, $receiver_id, $return_date)
    // {
    //     try {
    //         $sql = "INSERT INTO inventory_returned 
    //                     (assignment_id, returned_by, receiver_id, return_date, status, created_at, updated_at) 
    //                 VALUES 
    //                     (:assignment_id, :returned_by, :receiver_id, :return_date, 'pending', NOW(), NOW())";
            
    //         $stmt = $this->db->prepare($sql);
    //         return $stmt->execute([
    //             ':assignment_id' => $assignment_id,
    //             ':returned_by' => $returned_by_email,
    //             ':receiver_id' => $receiver_id,
    //             ':return_date' => $return_date
    //         ]);
    //     } catch (PDOException $e) {
    //         die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
    //     }
    // }
    public function recordReturn($assignment_id, $returned_by_email, $receiver_id, $return_date)
    {
        try {
            // 1️⃣ Insert return record
            $sql = "INSERT INTO inventory_returned 
                        (assignment_id, returned_by, receiver_id, return_date, status, created_at, updated_at) 
                    VALUES 
                        (:assignment_id, :returned_by, :receiver_id, :return_date, 'pending', NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':assignment_id' => $assignment_id,
                ':returned_by' => $returned_by_email,
                ':receiver_id' => $receiver_id,
                ':return_date' => $return_date
            ]);

            if (!$success) {
                error_log("Failed to insert return record for assignment: {$assignment_id}");
                echo "Failed to insert return record for assignment: {$assignment_id}<br>";
                return false;
            }

            // 2️⃣ Get assignment details
            $assignmentSql = "
                SELECT ia.id, ia.name AS staff_name, ia.managed_by AS manager_name, i.serial_number, i.tag_number
                FROM inventory_assignment ia
                INNER JOIN inventory i ON ia.item = i.id
                WHERE ia.id = :assignment_id
            ";
            $assignmentStmt = $this->db->prepare($assignmentSql);
            $assignmentStmt->execute([':assignment_id' => $assignment_id]);
            $assignmentData = $assignmentStmt->fetch(PDO::FETCH_ASSOC);

            if (!$assignmentData) {
                error_log("No assignment found with ID: {$assignment_id}");
                echo "No assignment found with ID: {$assignment_id}<br>";
                return false;
            }

            $staffName = $assignmentData['staff_name'];
            $managerName = $assignmentData['manager_name'];
            $itemDetails = [
                [
                    'serial_number' => $assignmentData['serial_number'],
                    'tag_number' => $assignmentData['tag_number']
                ]
            ];

            // 3️⃣ Lookup manager email by name
            $managerEmail = null;
            $managers = $this->getManagers(); // returns array with ['name' => 'Rita Kogi', 'email' => 'rita.kogi@example.com']

            foreach ($managers as $mgr) {
                if (trim(strtolower($mgr['name'])) === trim(strtolower($managerName))) {
                    $managerEmail = $mgr['email'];
                    break;
                }
            }

            if (!$managerEmail) {
                error_log("Manager email not found for: {$managerName}");
                echo "Manager email not found for: {$managerName}<br>";
            } else {
                // 4️⃣ Send email
                $this->sendReturnNotificationToManager(
                    $managerEmail,
                    $managerName,
                    $staffName,
                    $itemDetails,
                    $return_date
                );
            }

            return $assignmentData;

        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }
    protected function sendReturnNotificationToManager($managerEmail, $managerName, $staffName, $itemDetails, $returnDate)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // 🔹 Enable SMTP debug
            $mail->SMTPDebug = 2; // 0 = off, 1 = client, 2 = client+server
            $mail->Debugoutput = 'error_log'; // logs to PHP error_log

            // Email headers
            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');
            $mail->addAddress($managerEmail, $managerName);
            $mail->addBCC('information.systems@evidenceaction.org');

            // Email subject & body
            $mail->isHTML(true);
            $mail->Subject = "Notification: {$staffName} Returned Inventory Item(s)";

            $itemListHtml = "<ul>";
            foreach ($itemDetails as $item) {
                $serial = htmlspecialchars($item['serial_number'] ?? 'N/A');
                $tag = htmlspecialchars($item['tag_number'] ?? 'N/A');
                $itemListHtml .= "<li><strong>Serial Number:</strong> {$serial} <br> <strong>Tag Number:</strong> {$tag}</li>";
            }
            $itemListHtml .= "</ul>";

            $mail->Body = "
                <p>Dear {$managerName},</p>
                <p>Your supervisee <strong>{$staffName}</strong> has returned the following inventory item(s) on <strong>{$returnDate}</strong>:</p>
                {$itemListHtml}
                <p>The items are currently <strong>pending approval</strong>.</p>
                <p>Regards,<br>MLE Inventory Tool</p>
            ";

            $mail->AltBody = "{$staffName} has returned inventory item(s) on {$returnDate}. Items are pending approval.";

            if (!$mail->send()) {
                // 🔹 Output errors to browser for debugging
                echo "Mailer Error: " . $mail->ErrorInfo . "<br>";
                error_log("Mailer failed: " . $mail->ErrorInfo);
                return false;
            }

            echo "Email sent successfully to {$managerEmail}<br>";
            error_log("Return notification sent to: {$managerEmail}");
            return true;

        } catch (Exception $e) {
            // 🔹 Output exception details
            echo "PHPMailer Exception: " . $e->getMessage() . "<br>";
            error_log("PHPMailer Exception: " . $e->getMessage());
            error_log("PHPMailer Debug Info: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function getManagerFromAssignment($assignment_id)
    {
        $sql = "SELECT 
                    ia.managed_by,
                    sl.email
                FROM inventory_assignment ia
                LEFT JOIN staff_login sl 
                    ON CONCAT(
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 2)),
                        ' ',
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 2))
                    ) = ia.managed_by
                WHERE ia.id = :assignment_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getItemReturnStatus($assignment_id, $item_id)
    {
        $sql = "SELECT status FROM inventory_returned WHERE assignment_id = :assignment_id AND item_id = :item_id LIMIT 1";
        $query = $this->db->prepare($sql);

        $query->execute([
            ':assignment_id' => $assignment_id,
            ':item_id' => $item_id
        ]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get pending items for approval by the logged-in user
    public function getPendingApprovalsByUser($receiver_id)
    {
        $sql = "SELECT ir.*, inv.description, inv.serial_number, 
                    sl.email AS receiver_email,
                    SUBSTRING_INDEX(sl.email, '@', 1) AS name
                FROM inventory_returned ir
                JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                JOIN inventory inv ON ia.item = inv.id
                JOIN staff_login sl ON ir.receiver_id = sl.id
                WHERE ir.receiver_id = :receiver_id
                AND ir.status = 'pending'
                ORDER BY ir.return_date DESC";

        $query = $this->db->prepare($sql);

        if (!$query->execute([':receiver_id' => (int)$receiver_id])) {
            $errorInfo = $query->errorInfo();
            error_log("SQL Error: " . implode(", ", $errorInfo));
            return [];
        }

        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            error_log("No pending approvals found for receiver_id: $receiver_id");
        } else {
            error_log("Found " . count($results) . " pending approvals for receiver_id: $receiver_id");
        }

        return $results;
    }


    // Approve the returned item
    public function approveReturn($return_id, $item_state, $approved_by, $disapproval_comment = null) {
        try {
            if ($item_state === 'disapproved') {
                $sql = "UPDATE inventory_returned 
                        SET status = 'disapproved', 
                            item_state = 'disapproved',
                            approved_by = :approved_by,
                            approved_date = NOW(),
                            disapproval_comment = :disapproval_comment,
                            updated_at = NOW()
                        WHERE id = :return_id";
                $query = $this->db->prepare($sql);
                $query->execute([
                    ':approved_by' => $approved_by,
                    ':disapproval_comment' => $disapproval_comment,
                    ':return_id' => $return_id
                ]);
            } else {
                $sql = "UPDATE inventory_returned 
                        SET status = 'approved', 
                            item_state = :item_state, 
                            approved_by = :approved_by,
                            approved_date = NOW(),
                            updated_at = NOW()
                        WHERE id = :return_id";
                $query = $this->db->prepare($sql);
                $query->execute([
                    ':item_state' => $item_state,
                    ':approved_by' => $approved_by,
                    ':return_id' => $return_id
                ]);
            }
    
            return true;
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }
        
    public function getUserIdByEmail($email)
    {
        $sql = "SELECT id FROM staff_login WHERE email = :email LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute([':email' => $email]);
        return $query->fetchColumn(); // Returns the user ID
    }
    //lost items
    public function getLostItems()
    {
        $sql = "SELECT 
                    ia.item AS item_id, 
                    i.description, 
                    i.serial_number, 
                    i.tag_number, 
                    c.category, 
                    ir.return_date AS reported_date, 
                    ir.approved_date  -- Include approved_date here
                FROM inventory_returned ir
                JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                JOIN inventory i ON ia.item = i.id
                JOIN categories c ON i.category_id = c.id
                WHERE ir.item_state = 'lost'
                ORDER BY ir.approved_date DESC";  
    
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    //search lost item
    public function getLostItemsSearch($search)
    {
        try {
            $sql = "SELECT 
                        ia.item AS item_id, 
                        i.description, 
                        i.serial_number, 
                        i.tag_number, 
                        c.category, 
                        ir.return_date AS reported_date, 
                        ir.approved_date 
                    FROM inventory_returned ir
                    JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                    JOIN inventory i ON ia.item = i.id
                    JOIN categories c ON i.category_id = c.id
                    WHERE ir.item_state = 'lost'
                    AND (
                        LOWER(i.description) LIKE :search 
                        OR LOWER(i.serial_number) LIKE :search 
                        OR LOWER(i.tag_number) LIKE :search
                    )
                    ORDER BY ir.approved_date DESC";  

            $query = $this->db->prepare($sql);
            $query->execute(['search' => "%$search%"]); 
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }

    //damaged items
    public function getDamagedItems()
    {
        $sql = "SELECT 
                    ia.item AS item_id, 
                    i.description, 
                    i.serial_number, 
                    i.tag_number,  -- Added
                    c.category,  -- Added
                    ir.repair_status, 
                    ir.return_date AS reported_date
                FROM inventory_returned ir
                JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                JOIN inventory i ON ia.item = i.id
                JOIN categories c ON i.category_id = c.id  -- Joined categories table
                WHERE ir.item_state = 'damaged'
                ORDER BY ir.return_date DESC";
    
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    //search damaged items
    public function getDamagedItemsSearch($search)
    {
        try {
            $sql = "SELECT 
                        ia.item AS item_id, 
                        i.description, 
                        i.serial_number, 
                        i.tag_number,  
                        c.category,  
                        ir.repair_status, 
                        ir.return_date AS reported_date
                    FROM inventory_returned ir
                    JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                    JOIN inventory i ON ia.item = i.id
                    JOIN categories c ON i.category_id = c.id  
                    WHERE ir.item_state = 'damaged'
                    AND (
                        LOWER(i.description) LIKE :search 
                        OR LOWER(i.serial_number) LIKE :search 
                        OR LOWER(i.tag_number) LIKE :search
                    )
                    ORDER BY ir.return_date DESC";

            $query = $this->db->prepare($sql);
            $query->execute(['search' => "%$search%"]);

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }

    //dissapproved items
    public function getDisapprovedItems()
    {
        $sql = "SELECT 
                    ia.item AS item_id, 
                    i.description, 
                    i.serial_number, 
                    i.tag_number, 
                    c.category,
                    ir.disapproval_comment,
                    ir.return_date AS disapproved_date,
                    ir.returned_by,
                    sl.email AS receiver_email
                FROM inventory_returned ir
                JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                JOIN inventory i ON ia.item = i.id
                JOIN categories c ON i.category_id = c.id
                LEFT JOIN staff_login sl ON sl.id = ir.receiver_id
                WHERE ir.item_state = 'disapproved'
                ORDER BY ir.return_date DESC";
        
        $query = $this->db->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Format names
        foreach ($results as &$row) {
            $row['returned_by'] = ucwords(str_replace('.', ' ', strtok($row['returned_by'], '@')));
            $row['receiver_name'] = $row['receiver_email'] 
                ? ucwords(str_replace('.', ' ', strtok($row['receiver_email'], '@')))
                : 'N/A';
        }

        return $results;
    }
    //approving ststus of damaged items
    public function updateRepairStatus($item_id, $repair_status)
    {
        // Step 1: Get assignment_id from inventory_assignment
        $query = "SELECT id FROM inventory_assignment WHERE item = :item_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':item_id' => $item_id]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$assignment) {
            die("DEBUG: No assignment found for item_id = $item_id");
        }
    
        $assignment_id = $assignment['id'];
        echo "DEBUG: Found assignment_id = $assignment_id<br>";
    
        // Step 2: Check if inventory_returned record exists
        $check = $this->db->prepare("SELECT id FROM inventory_returned WHERE assignment_id = :assignment_id");
        $check->execute([':assignment_id' => $assignment_id]);
        $returnRecord = $check->fetch(PDO::FETCH_ASSOC);
    
        if (!$returnRecord) {
            // Optional: Insert a record if missing
            $insert = $this->db->prepare("INSERT INTO inventory_returned (assignment_id, repair_status, created_at) VALUES (:assignment_id, :repair_status, NOW())");
            $inserted = $insert->execute([
                ':assignment_id' => $assignment_id,
                ':repair_status' => $repair_status
            ]);
    
            if ($inserted) {
                echo "DEBUG: Inserted new inventory_returned record with repair_status = $repair_status.";
                return true;
            } else {
                die("DEBUG: Failed to insert new return record.");
            }
        }
    
        // Step 3: Update if record exists
        $sql = "UPDATE inventory_returned SET repair_status = :repair_status WHERE assignment_id = :assignment_id";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':repair_status' => $repair_status,
            ':assignment_id' => $assignment_id
        ]);
    
        echo "DEBUG: Rows affected: " . $stmt->rowCount();
    
        if (!$success || $stmt->rowCount() === 0) {
            die("DEBUG: Update query failed or no rows affected.");
        }
    
        echo "DEBUG: Repair status updated successfully for assignment_id = $assignment_id!";
        return true;
    }
    
    public function getAssignedItems()
    {
        $sql = "SELECT 
                    ia.id,
                    REPLACE(SUBSTRING_INDEX(ia.email, '@', 1), '.', ' ') AS user_name_raw,
                    ia.email AS assigned_user_email,
                    d.department_name AS department,
                    p.position_name AS position,
                    c.category AS category,
                    i.description,
                    ia.serial_number,
                    ia.tag_number,
                    ia.date_assigned,
                    ia.managed_by,
                    ia.acknowledgment_status,
                    ia.device_state, -- ✅ Added here
                    ia.created_at,
                    ia.updated_at
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN categories c ON i.category_id = c.id  
                LEFT JOIN staff_login sl ON ia.email = sl.email
                LEFT JOIN departments d ON sl.department = d.id
                LEFT JOIN positions p ON sl.position = p.id
                WHERE ia.acknowledgment_status = 'acknowledged'
                AND NOT EXISTS (
                    SELECT 1 FROM inventory_returned ir 
                    WHERE ir.assignment_id = ia.id 
                    AND (
                        ir.item_state = 'functional' AND ir.status = 'approved'
                        OR ir.item_state = 'lost'
                        OR ir.item_state = 'damaged'
                    )
                )";

        $query = $this->db->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$row) {
            $row['user_name'] = ucwords($row['user_name_raw']);
            unset($row['user_name_raw']);
        }

        return $results;
    }

    //disposed items ie items that cant be repaired and lost
    public function getDisposedItems()
    {
        $sql = "SELECT 
                    ia.item AS item_id, 
                    i.description, 
                    i.serial_number, 
                    i.tag_number, 
                    c.category AS category_name, 
                    ir.returned_by, 
                    ir.item_state,  
                    ir.repair_status, 
                    ir.return_date AS reported_date, 
                    ir.approved_date, 
                    CASE 
                        WHEN ir.item_state = 'lost' THEN 'Lost' 
                        WHEN ir.repair_status = 'Unrepairable' THEN 'Unrepairable' 
                        ELSE 'Disposed' 
                    END AS reason
                FROM inventory_returned ir
                JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                JOIN inventory i ON ia.item = i.id
                JOIN categories c ON i.category_id = c.id
                WHERE ir.item_state = 'lost' OR ir.repair_status = 'Unrepairable'
                ORDER BY ir.approved_date DESC";

        $query = $this->db->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Format returned_by to 'Rita Kogi' from 'rita.kogi@example.com'
        foreach ($results as &$row) {
            $row['returned_by'] = ucwords(str_replace('.', ' ', strtok($row['returned_by'], '@')));
        }

        return $results;
    }
    //search disposed items
    public function getDisposedItemsSearch($search)
    {
        try {
            $sql = "SELECT 
                        ia.item AS item_id, 
                        i.description, 
                        i.serial_number, 
                        i.tag_number, 
                        c.category AS category_name, 
                        ir.returned_by, 
                        ir.item_state,  
                        ir.repair_status, 
                        ir.return_date AS reported_date, 
                        ir.approved_date, 
                        CASE 
                            WHEN ir.item_state = 'lost' THEN 'Lost' 
                            WHEN ir.repair_status = 'Unrepairable' THEN 'Unrepairable' 
                            ELSE 'Disposed' 
                        END AS reason
                    FROM inventory_returned ir
                    JOIN inventory_assignment ia ON ir.assignment_id = ia.id
                    JOIN inventory i ON ia.item = i.id
                    JOIN categories c ON i.category_id = c.id
                    WHERE (ir.item_state = 'lost' OR ir.repair_status = 'Unrepairable')
                    AND (
                        LOWER(i.description) LIKE :search 
                        OR LOWER(i.serial_number) LIKE :search 
                        OR LOWER(i.tag_number) LIKE :search
                    )
                    ORDER BY ir.approved_date DESC";

            $query = $this->db->prepare($sql);
            $query->execute(['search' => "%$search%"]);

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }

    public function getUnassignedItems()
    {
        try {
            $sql = "
                SELECT 
                    i.id, 
                    c.category AS category,  
                    i.description, 
                    i.serial_number, 
                    i.tag_number,
                    i.custodian,
                    SUBSTRING_INDEX(sl.email, '@', 1) AS custodian_name,
                    sl.dutystation AS location_id,
                    loc.location_name AS location_name
                FROM inventory i
                LEFT JOIN categories c ON i.category_id = c.id
                LEFT JOIN staff_login sl ON i.custodian = sl.id
                LEFT JOIN locations loc ON sl.dutystation = loc.id
                WHERE 
                    i.id NOT IN (
                        SELECT ia.item FROM inventory_assignment ia
                        WHERE ia.acknowledgment_status IN ('pending', 'approved', 'acknowledged')
                    )
                    AND (
                        i.id NOT IN (
                            SELECT ia.item FROM inventory_assignment ia
                            JOIN inventory_returned ir ON ia.id = ir.assignment_id
                            WHERE ir.item_state = 'lost' 
                        )
                        OR i.id IN (
                            SELECT ia.item FROM inventory_assignment ia
                            JOIN inventory_returned ir ON ia.id = ir.assignment_id
                            WHERE ir.item_state = 'functional' AND ir.status = 'approved'
                        )
                        OR i.id IN (
                            SELECT ia.item FROM inventory_assignment ia
                            JOIN inventory_returned ir ON ia.id = ir.assignment_id
                            WHERE ir.item_state = 'damaged' AND ir.repair_status = 'Repairable'
                        )
                    )
            ";

            $query = $this->db->prepare($sql);
            $query->execute();
            $unassignedItems = $query->fetchAll(PDO::FETCH_ASSOC);

            return $unassignedItems;

        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }
    //search in the instock page
    public function getUnassignedItemsSearch($search)
    {
        try {
            $sql = "SELECT 
                        i.id, 
                        c.category AS category,  
                        i.description, 
                        i.serial_number, 
                        i.tag_number 
                    FROM inventory i
                    LEFT JOIN categories c ON i.category_id = c.id
                    WHERE 
                        (
                            i.id NOT IN (
                                SELECT ia.item FROM inventory_assignment ia
                                JOIN inventory_returned ir ON ia.id = ir.assignment_id
                                WHERE ir.item_state = 'lost' 
                            )
                            OR i.id IN (
                                SELECT ia.item FROM inventory_assignment ia
                                JOIN inventory_returned ir ON ia.id = ir.assignment_id
                                WHERE ir.item_state = 'functional'
                                AND ir.status = 'approved' 
                            )
                            OR i.id IN (
                                SELECT ia.item FROM inventory_assignment ia
                                JOIN inventory_returned ir ON ia.id = ir.assignment_id
                                WHERE ir.item_state = 'damaged'
                                AND ir.repair_status = 'Repairable' 
                            )
                        )
                        -- Search condition
                        AND (
                            i.serial_number LIKE :search 
                            OR i.tag_number LIKE :search
                        )";
    
            $query = $this->db->prepare($sql);
            $query->execute([':search' => "%$search%"]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }

    //positions model
    // Fetch all positions ordered by hierarchy level
    public function getPositions()
    {
        $sql = "SELECT * FROM positions ORDER BY hierarchy_level ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a new position
    public function addPosition($position_name, $hierarchy_level)
    {
        $sql = "INSERT INTO positions (position_name, hierarchy_level) VALUES (:position_name, :hierarchy_level)";
        $query = $this->db->prepare($sql);
        $parameters = array(':position_name' => $position_name, ':hierarchy_level' => $hierarchy_level);
        return $query->execute($parameters);
    }

    // Get a single position by ID
    public function getPositionById($id)
    {
        $sql = "SELECT * FROM positions WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        $query->execute($parameters);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Update an existing position
    public function updatePosition($id, $position_name, $hierarchy_level)
    {
        $sql = "UPDATE positions SET position_name = :position_name, hierarchy_level = :hierarchy_level WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':position_name' => $position_name, ':hierarchy_level' => $hierarchy_level, ':id' => $id);
        return $query->execute($parameters);
    }

    // Delete a position
    public function deletePosition($id)
    {
        $sql = "DELETE FROM positions WHERE id = :id";
        $query = $this->db->prepare($sql);
        $parameters = array(':id' => $id);
        return $query->execute($parameters);
    }
    
    //department model
    // Fetch all departments
    public function getAllDepartments() {
        return $this->db->query("SELECT * FROM departments ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Fetch departments with parent-child structure
    public function getDepartmentsHierarchy() {
        return $this->db->query("
            SELECT d1.id, d1.department_name, d1.parent_id, d2.department_name AS parent_name
            FROM departments d1
            LEFT JOIN departments d2 ON d1.parent_id = d2.id
            ORDER BY d1.parent_id ASC, d1.department_name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Get department by ID
    public function getDepartmentById($id) {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Add new department with parent ID
    public function addDepartment($department_name, $parent_id = NULL) {
        $stmt = $this->db->prepare("INSERT INTO departments (department_name, parent_id) VALUES (:department_name, :parent_id)");
        $stmt->bindParam(':department_name', $department_name);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_NULL | PDO::PARAM_INT);
        return $stmt->execute();
    }

      // Update department details including parent_id
    public function updateDepartment($id, $department_name, $parent_id = NULL) 
    {
        $stmt = $this->db->prepare("UPDATE departments SET department_name = :department_name, parent_id = :parent_id WHERE id = :id");
        $stmt->bindParam(':department_name', $department_name, PDO::PARAM_STR);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_NULL | PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Delete a department
    public function deleteDepartment($id) {
        $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
     //managers reports
    //hierachy access of assignments
    public function getAssignmentsByHierarchy($loggedInEmail)
    {
        $sql = "SELECT p.hierarchy_level AS position_level, d.id AS department_id
                FROM staff_login sl
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN departments d ON sl.department = d.id
                WHERE sl.email = :email";
    
        $query = $this->db->prepare($sql);
        $query->bindParam(':email', $loggedInEmail, PDO::PARAM_STR);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_OBJ);
    
        if (!$user) {
            return [];
        }
    
        $userLevel = (int) $user->position_level;
        $userDepartment = (int) $user->department_id;
    
        $subDepartments = $this->getSubDepartments($userDepartment);
        $subDepartments[] = $userDepartment;  
    
        $allowedLevels = [];
        switch ($userLevel) {
            case 1:  
                $allowedLevels = [1, 2, 3, 4, 5, 6];
                break;
            case 2:  
                $allowedLevels = [3, 4, 5, 6];
                break;
            case 3:  
                $allowedLevels = [4, 5, 6];
                break;
            case 4:  
                $allowedLevels = [5, 6];
                break;
            default:
                return [];
        }
    
        // Fetch assignments for staff within the department hierarchy
        $sql = "SELECT 
                    ia.id,
                    CONCAT(
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 2)),
                        ' ',
                        UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 1)),
                        LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 2))
                    ) AS user_name,
                    sl.email,
                    d.department_name AS department,  
                    p.position_name AS position,    
                    i.category_id,
                    i.description,
                    ia.serial_number,
                    ia.tag_number,
                    ia.date_assigned,
                    ia.managed_by,
                    ia.acknowledgment_status,
                    ia.created_at,
                    ia.updated_at
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN staff_login sl ON ia.email = sl.email
                LEFT JOIN departments d ON sl.department = d.id  
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id  
                WHERE sl.department IN (" . implode(',', array_fill(0, count($subDepartments), '?')) . ")  
                AND p.hierarchy_level IN (" . implode(',', $allowedLevels) . ")  
                AND ir.assignment_id IS NULL";
    
        $query = $this->db->prepare($sql);
        foreach ($subDepartments as $index => $deptId) {
            $query->bindValue($index + 1, $deptId, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    private function getSubDepartments($departmentId)
    {
        $sql = "WITH RECURSIVE sub_departments AS (
                    SELECT id FROM departments WHERE parent_id = :departmentId
                    UNION ALL
                    SELECT d.id FROM departments d
                    INNER JOIN sub_departments sd ON d.parent_id = sd.id
                ) 
                SELECT id FROM sub_departments";

        $query = $this->db->prepare($sql);
        $query->bindParam(':departmentId', $departmentId, PDO::PARAM_INT);
        $query->execute();
        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'id');
    }
    
    //hierachy access of returned items
    public function getReturnedItemsByHierarchy($loggedInEmail)
    {
        try {

            $sql = "SELECT p.hierarchy_level, sl.department 
                    FROM staff_login sl
                    LEFT JOIN positions p ON sl.position = p.id
                    WHERE sl.email = :email";

            $query = $this->db->prepare($sql);
            $query->bindParam(':email', $loggedInEmail, PDO::PARAM_STR);
            $query->execute();
            $user = $query->fetch(PDO::FETCH_OBJ);

            if (!$user) {
                return []; 
            }

            $userLevel = (int) $user->hierarchy_level;
            $userDepartment = (int) $user->department;


            $subDepartments = $this->getSubDepartments($userDepartment);
            $subDepartments[] = $userDepartment;

            $allowedLevels = [];
            switch ($userLevel) {
                case 1: $allowedLevels = [2, 3, 4, 5]; break;
                case 2: $allowedLevels = [3, 4, 5]; break;
                case 3: $allowedLevels = [4, 5]; break;
                default: return []; 
            }

            $levelPlaceholders = implode(',', array_fill(0, count($allowedLevels), '?'));
            $departmentPlaceholders = implode(',', array_fill(0, count($subDepartments), '?'));

            $sql = "SELECT 
                        ir.id, 
                        i.description, 
                        i.serial_number, 
                        CONCAT(
                            UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 1)),
                            LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', 1), 2)),
                            ' ',
                            UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 1)),
                            LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.email, '@', 1), '.', -1), 2))
                        ) AS returned_by_name,
                        ir.return_date, 
                        ir.status, 
                        CONCAT(
                            UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', 1), 1)),
                            LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', 1), 2)),
                            ' ',
                            UPPER(LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', -1), 1)),
                            LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(sl_receiver.email, '@', 1), '.', -1), 2))
                        ) AS receiver_name
                    FROM inventory_returned ir
                    INNER JOIN inventory_assignment ia ON ir.assignment_id = ia.id  
                    INNER JOIN inventory i ON ia.item = i.id  
                    INNER JOIN staff_login sl ON ir.returned_by = sl.email
                    INNER JOIN staff_login sl_receiver ON ir.receiver_id = sl_receiver.id  
                    INNER JOIN positions p ON sl.position = p.id
                    WHERE p.hierarchy_level IN ($levelPlaceholders)
                    AND sl.department IN ($departmentPlaceholders)
                    AND ir.returned_by != ?";

            $params = array_merge($allowedLevels, $subDepartments, [$loggedInEmail]);

            $query = $this->db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }
 
    // downloading managers reports
    public function getAssignmentsForDownload($loggedInEmail)
    {
        $sql = "SELECT p.hierarchy_level AS position_level, d.id AS department_id
                FROM staff_login sl
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN departments d ON sl.department = d.id
                WHERE sl.email = :email";
    
        $query = $this->db->prepare($sql);
        $query->bindParam(':email', $loggedInEmail, PDO::PARAM_STR);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_OBJ);
    
        if (!$user) {
            return [];
        }
    
        $userLevel = (int) $user->position_level;
        $userDepartment = (int) $user->department_id;
    
        $subDepartments = $this->getSubDepartments($userDepartment);
        $subDepartments[] = $userDepartment;  
    
        $allowedLevels = [];
        switch ($userLevel) {
            case 1:  
                $allowedLevels = [1, 2, 3, 4, 5, 6];
                break;
            case 2:  
                $allowedLevels = [3, 4, 5, 6];
                break;
            case 3:  
                $allowedLevels = [4, 5, 6];
                break;
            case 4:  
                $allowedLevels = [5, 6];
                break;
            default:
                return [];
        }
    
        $sql = "SELECT 
                    ia.id,
                    sl.email AS user_email,
                    d.department_name AS department,  
                    p.position_name AS position,    
                    i.category_id,
                    i.description,
                    ia.serial_number,
                    ia.tag_number,
                    ia.date_assigned,
                    ia.managed_by,
                    ia.acknowledgment_status,
                    ia.created_at
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN staff_login sl ON ia.email = sl.email
                LEFT JOIN departments d ON sl.department = d.id  
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id  
                WHERE sl.department IN (" . implode(',', array_fill(0, count($subDepartments), '?')) . ")  
                AND p.hierarchy_level IN (" . implode(',', $allowedLevels) . ")  
                AND ir.assignment_id IS NULL";
    
        $query = $this->db->prepare($sql);
        foreach ($subDepartments as $index => $deptId) {
            $query->bindValue($index + 1, $deptId, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getReturnedItemsForDownload($loggedInEmail)
    {
        try {

            $sql = "SELECT p.hierarchy_level, sl.department AS department_id
                    FROM staff_login sl
                    LEFT JOIN positions p ON sl.position = p.id
                    WHERE sl.email = ?";
    
            $query = $this->db->prepare($sql);
            $query->execute([$loggedInEmail]);
            $user = $query->fetch(PDO::FETCH_OBJ);
    
            if (!$user) {
                return [];
            }
    
            $userLevel = (int) $user->hierarchy_level;
            $userDepartment = (int) $user->department_id;

            $subDepartments = $this->getSubDepartments($userDepartment);
            $subDepartments[] = $userDepartment;

            $allowedLevels = [];
            switch ($userLevel) {
                case 1: $allowedLevels = [1, 2, 3, 4, 5, 6]; break;
                case 2: $allowedLevels = [3, 4, 5, 6]; break;
                case 3: $allowedLevels = [4, 5, 6]; break;
                case 4: $allowedLevels = [5, 6]; break;
                default: return [];
            }
    

            $placeholders = implode(',', array_fill(0, count($subDepartments), '?'));
            $sql = "SELECT ir.id, i.description, i.serial_number, 
                            SUBSTRING_INDEX(sl.email, '@', 1) AS returned_by_name,  
                            ir.return_date, ir.status, 
                            SUBSTRING_INDEX(sl_receiver.email, '@', 1) AS receiver_name
                    FROM inventory_returned ir
                    INNER JOIN inventory_assignment ia ON ir.assignment_id = ia.id  
                    INNER JOIN inventory i ON ia.item = i.id  
                    INNER JOIN staff_login sl ON ir.returned_by = sl.email
                    INNER JOIN staff_login sl_receiver ON ir.receiver_id = sl_receiver.id  
                    INNER JOIN positions p ON sl.position = p.id
                    WHERE sl.department IN ($placeholders)  
                    AND p.hierarchy_level IN (" . implode(',', $allowedLevels) . ")  
                    AND ir.returned_by != ?";
    

            $query = $this->db->prepare($sql);
            $params = array_merge($subDepartments, [$loggedInEmail]);
            $query->execute($params);
    
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<br><strong>SQL Exception:</strong> " . $e->getMessage());
        }
    }
    
    // Fetch the counts for the dashboards
    public function getItemStates()
    {
        $query = "
            SELECT 
                SUM(CASE 
                    WHEN ir.item_state = 'functional' AND ir.assignment_id IS NULL THEN 1
                    WHEN ir.item_state = 'functional' THEN 1
                    ELSE 0 
                END) AS functional,
                SUM(CASE 
                    WHEN ir.item_state = 'lost' THEN 1 
                    ELSE 0 
                END) AS lost,
                SUM(CASE 
                    WHEN ir.item_state = 'damaged' THEN 1 
                    ELSE 0 
                END) AS damaged
            FROM inventory_assignment ia
            LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id 
        ";
        
        $result = $this->db->query($query);
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        return $data;
    }
    
    public function getInUseCount() {
        $sql = "SELECT COUNT(*) AS in_use_count
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id
                WHERE ir.assignment_id IS NULL 
                AND ia.acknowledgment_status = 'acknowledged'";
    
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['in_use_count'];
    }
    
    public function getInStockCount() {
        $sql = "SELECT COUNT(*) AS in_stock_count
                FROM inventory i
                LEFT JOIN inventory_assignment ia ON i.id = ia.item
                LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id
                WHERE (ia.id IS NULL 
                        OR (ir.assignment_id IS NOT NULL 
                            AND (ir.item_state = 'functional' AND ir.status = 'approved')
                            OR (ir.item_state = 'damaged' AND ir.repair_status = 'Repairable'))
                       )";
    
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['in_stock_count'];
    }
    
    public function getItemCountsByCategory() {
        $sql = "SELECT 
                    c.category AS category_name,
                    COUNT(i.id) AS item_count
                FROM inventory i
                LEFT JOIN categories c ON i.category_id = c.id
                GROUP BY c.category";
    
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get_user_by_email($email)
    {
        $sql = "SELECT 
                    sl.*, 
                    d.department_name, 
                    p.position_name, 
                    CONCAT(loc.location_name, ' - ', o.office_name) as dutystation
                FROM staff_login sl
                LEFT JOIN departments d ON sl.department = d.id
                LEFT JOIN positions p ON sl.position = p.id
                LEFT JOIN offices o ON sl.dutystation = o.id
                LEFT JOIN locations loc ON o.location_id = loc.id
                WHERE sl.email = :email
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /** ---------------- Confirming Items Periodically Models -------------------- **/
    // 1. Set the admin button to enable or disable reconfirmation
    public function updateReconfirmStatusForAll($enabled)
    {
        $sql = "UPDATE inventory_assignment SET reconfirm_enabled = :enabled";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':enabled' => $enabled ? 1 : 0]);
    }

    // 2. Add the confirm button for users to mark assignments as confirmed
    public function confirmAssignment($assignmentId, $sessionId)
    {
        $sql = "UPDATE inventory_assignment 
                SET confirmed = 1, 
                    confirmation_date = NOW(), 
                    reconfirmation_session_id = :session_id 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $assignmentId,
            ':session_id' => $sessionId
        ]);
    }
    
    // 3. Get items not confirmed
    public function allAssignmentsConfirmed()
    {
        $sql = "SELECT COUNT(*) AS unconfirmed 
                FROM inventory_assignment 
                WHERE reconfirm_enabled = 1 AND confirmed = 0";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return isset($result['unconfirmed']) && $result['unconfirmed'] == 0;
    }
    
    //4.Reset reconfirm_enabled after all confirm
    public function resetReconfirmToggle()
    {
        $sql = "UPDATE inventory_assignment 
                SET reconfirm_enabled = 0 
                WHERE reconfirm_enabled = 1";
    
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
    
    //annual reports
    public function startNewReconfirmationSession($initiated_by)
    {
        $sql = "INSERT INTO reconfirmation_sessions (year, month, initiated_by, start_date, active) 
                VALUES (:year, :month, :initiated_by, :start_date, 1)";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':year' => date('Y'),
            ':month' => date('m'),
            ':initiated_by' => $initiated_by,
            ':start_date' => date('Y-m-d'),
        ]);

        if ($success) {
            return $this->db->lastInsertId();
        } else {
            return false; 
        }
    }
    
    public function getActiveReconfirmationSession() {
        $sql = "SELECT * 
                FROM reconfirmation_sessions 
                WHERE active = 1 
                ORDER BY id DESC 
                LIMIT 1";
    
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result : null;
    }
    
    public function deactivateReconfirmationSession($sessionId)
    {
        $sql = "UPDATE reconfirmation_sessions SET active = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $sessionId]);
    }

    public function assignSessionToUnconfirmed($session_id)
    {
        if (!is_numeric($session_id)) {
            throw new InvalidArgumentException("Invalid session_id passed to assignSessionToUnconfirmed.");
        }

        $sql = "UPDATE inventory_assignment 
                SET reconfirm_enabled = 1, 
                    confirmed = 0, 
                    confirmation_date = NULL, 
                    reconfirmation_session_id = :session_id 
                WHERE reconfirm_enabled = 0";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':session_id' => $session_id]);
    }

    //recording each confirmation
    public function recordConfirmation($inventoryAssignmentId, $status, $confirmedBy)
    {
        $sql = "INSERT INTO confirmation_log (inventory_assignment_id, confirmation_date, confirmed_by, status)
                VALUES (:inventory_assignment_id, DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i'), :confirmed_by, :status)";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':inventory_assignment_id' => $inventoryAssignmentId, 
            ':confirmed_by' => $confirmedBy, 
            ':status' => $status 
        ]);
    }
    
    //getting all reports
    public function getReconfirmationReport($year = null, $month = null)
    {
        $sql = "SELECT 
                    CONCAT(UCASE(LEFT(SUBSTRING_INDEX(ia.email, '@', 1), 1)), 
                    LCASE(SUBSTRING(SUBSTRING_INDEX(ia.email, '@', 1), 2))) AS name, 
                    ia.email,
                    p.position_name AS position,  
                    d.department_name AS department,
                    ia.serial_number,
                    ia.tag_number,
                    i.description AS item,
                    ia.managed_by,
                    ia.date_assigned,
                    rs.year,
                    rs.month,
                    COALESCE(cl.confirmation_date, '') AS log_confirmation_date,
                    COALESCE(cl.confirmed_by, '') AS confirmed_by,
                    COALESCE(cl.status, 'Pending') AS confirmation_status
                FROM inventory_assignment ia
                JOIN reconfirmation_sessions rs ON ia.reconfirmation_session_id = rs.id
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN confirmation_log cl ON ia.id = cl.inventory_assignment_id
                LEFT JOIN staff_login sl ON ia.email = sl.email  
                LEFT JOIN positions p ON sl.position = p.id 
                LEFT JOIN departments d ON sl.department = d.id
                WHERE 1 = 1"; // no filter on ia.confirmed

        $params = [];

        if ($year !== null) {
            $sql .= " AND rs.year = :year";
            $params[':year'] = $year;
        }

        if ($month !== null) {
            $sql .= " AND rs.month = :month";
            $params[':month'] = $month;
        }

        $sql .= " ORDER BY ia.date_assigned DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentsNeedingConfirmation()
    {
        $sql = "SELECT ia.email,
                    SUBSTRING_INDEX(ia.email, '@', 1) AS raw_name,
                    i.description,
                    ia.tag_number,
                    ia.serial_number
                FROM inventory_assignment ia
                LEFT JOIN inventory i ON ia.item = i.id
                LEFT JOIN inventory_returned ir ON ia.id = ir.assignment_id
                WHERE ia.confirmed = 0
                AND ia.reconfirm_enabled = 1
                AND ia.acknowledgment_status = 'acknowledged'
                AND (ir.id IS NULL OR ir.status = 'pending')
                ORDER BY ia.email";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($assignments as $row) {
            $email = $row['email'];
            if (!isset($grouped[$email])) {
                $grouped[$email] = [
                    'name' => $row['raw_name'],
                    'assignments' => [],
                ];
            }
            $grouped[$email]['assignments'][] = [
                'description' => $row['description'],
                'tag_number' => $row['tag_number'],
                'serial_number' => $row['serial_number']
            ];
        }

        return $grouped;
    }

    /** ---------------- FAQs Models -------------------- **/
    //faqs-search
    public function searchFAQs($keyword)
    {
        // Convert to lowercase and split into words
        $words = preg_split('/\s+/', strtolower(trim($keyword)));

        // Remove filler/common words
        $stopWords = ['how', 'do', 'i', 'the', 'a', 'an', 'to', 'for', 'is', 'on', 'in', 'and', 'you', 'my', 'your'];
        $filteredWords = array_diff($words, $stopWords);

        // If nothing meaningful left, use all words
        if (empty($filteredWords)) {
            $filteredWords = $words;
        }

        // Build dynamic WHERE clause
        $conditions = [];
        foreach ($filteredWords as $index => $word) {
            $param = ":word$index";
            $conditions[] = "(LOWER(question) LIKE $param OR LOWER(answer) LIKE $param)";
        }

        // Safety check
        if (empty($conditions)) return [];

        // Create a relevance score (counts how many words match)
        $relevance = [];
        foreach ($filteredWords as $index => $word) {
            $relevance[] = "(CASE WHEN LOWER(question) LIKE :rel$index OR LOWER(answer) LIKE :rel$index THEN 1 ELSE 0 END)";
        }

        $sql = "
            SELECT *, (" . implode(" + ", $relevance) . ") AS relevance
            FROM faqs
            WHERE " . implode(" OR ", $conditions) . "
            ORDER BY relevance DESC, id ASC
            LIMIT 5
        ";

        $query = $this->db->prepare($sql);

        // Bind words twice (for WHERE and relevance scoring)
        foreach ($filteredWords as $index => $word) {
            $like = "%" . $word . "%";
            $query->bindValue(":word$index", $like, PDO::PARAM_STR);
            $query->bindValue(":rel$index", $like, PDO::PARAM_STR);
        }

        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    //fetch all FAQs (for admin view)
    public function getAllFaqs()
    {
        $sql = "SELECT * FROM faqs ORDER BY id DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // add new FAQ
    public function addFaq($question, $answer)
    {
        $sql = "INSERT INTO faqs (question, answer) VALUES (:question, :answer)";
        $query = $this->db->prepare($sql);
        return $query->execute([
            ':question' => $question,
            ':answer' => $answer
        ]);
    }
    // update existing FAQ
    public function updateFaq($id, $question, $answer)
    {
        $sql = "UPDATE faqs 
                SET question = :question, 
                    answer = :answer 
                WHERE id = :id";
        $query = $this->db->prepare($sql);
        return $query->execute([
            ':id' => $id,
            ':question' => $question,
            ':answer' => $answer
        ]);
    }

    // delete FAQ
    public function deleteFaq($id)
    {
        $sql = "DELETE FROM faqs WHERE id = :id";
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $id]);
    }

        //log in the searched question
    public function logFaqSearch($user_email, $user_name, $text)
    {
        error_log("=== logFaqSearch called ===");
        error_log("Email: $user_email");
        error_log("Name: $user_name");
        error_log("Text: $text");
        
        try {
            $sql = "INSERT INTO faq_activity (user_email, user_name, searched_text)
                    VALUES (:email, :name, :text)";
            $query = $this->db->prepare($sql);
            
            $params = [
                ':email' => $user_email,
                ':name'  => $user_name,
                ':text'  => $text
            ];
            
            error_log("Executing SQL with params: " . print_r($params, true));
            $result = $query->execute($params);
            
            $lastId = $this->db->lastInsertId();
            error_log("✅ Search logged successfully. Insert ID: $lastId");
            
            return $lastId; // RETURN THE ID!
            
        } catch (PDOException $e) {
            error_log("❌ Database error in logFaqSearch: " . $e->getMessage());
            return false;
        }
    }

    // Update existing search record with faq_id
    public function updateFaqClick($search_log_id, $faq_id)
    {
        error_log("=== updateFaqClick called ===");
        error_log("Search Log ID: $search_log_id");
        error_log("FAQ ID: $faq_id");
        
        try {
            $sql = "UPDATE faq_activity 
                    SET faq_id = :faq_id 
                    WHERE id = :id AND faq_id IS NULL";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':faq_id', $faq_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $search_log_id, PDO::PARAM_INT);

            $result = $stmt->execute();
            
            if ($result) {
                $rowsAffected = $stmt->rowCount();
                error_log("✅ FAQ click updated successfully. Rows affected: $rowsAffected");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ Update failed. Error info: " . print_r($errorInfo, true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("❌ PDO Exception in updateFaqClick: " . $e->getMessage());
            return false;
        }
    }

        // Log click
    public function logFaqClick($user_email, $user_name, $faq_id)
    {
        error_log("=== logFaqClick called ===");
        error_log("Email: $user_email");
        error_log("Name: $user_name");
        error_log("FAQ ID: $faq_id");
        
        try {
            $sql = "INSERT INTO faq_activity (user_email, user_name, faq_id)
                    VALUES (:email, :name, :faq_id)";
            $stmt = $this->db->prepare($sql);
            
            error_log("SQL: $sql");
            error_log("Binding params: email=$user_email, name=$user_name, faq_id=$faq_id");
            
            $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
            $stmt->bindParam(':name', $user_name, PDO::PARAM_STR);
            $stmt->bindParam(':faq_id', $faq_id, PDO::PARAM_INT);

            $result = $stmt->execute();
            
            if ($result) {
                $lastId = $this->db->lastInsertId();
                error_log("✅ Click logged successfully. Insert ID: $lastId");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ Execute failed. Error info: " . print_r($errorInfo, true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("❌ PDO Exception in logFaqClick: " . $e->getMessage());
            error_log("❌ SQL error code: " . $e->getCode());
            error_log("❌ SQL error info: " . $e->errorInfo);
            return false;
        }
    }

    //analytics most searched and most viewed
    public function getTopSearches()
    {
        $sql = "SELECT searched_text, COUNT(*) AS total
                FROM faq_activity
                WHERE searched_text IS NOT NULL
                GROUP BY searched_text
                ORDER BY total DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTopViewedFaqs()
    {
        $sql = "
            SELECT 
                f.id,
                f.question,
                COUNT(a.id) AS total
            FROM faqs f
            LEFT JOIN faq_activity a 
                ON f.id = a.faq_id
            GROUP BY f.id, f.question
            ORDER BY total DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTopUsers()
    {
        $sql = "
            SELECT 
                CASE 
                    WHEN a.user_email LIKE '%.%@%' THEN 
                        -- For emails with dots in local part (rita.kogi@gmail.com)
                        CONCAT(
                            UPPER(SUBSTRING(SUBSTRING_INDEX(a.user_email, '.', 1), 1, 1)),
                            LOWER(SUBSTRING(SUBSTRING_INDEX(a.user_email, '.', 1), 2)),
                            ' ',
                            UPPER(SUBSTRING(
                                SUBSTRING_INDEX(
                                    SUBSTRING_INDEX(a.user_email, '@', 1),
                                    '.',
                                    -1
                                ), 1, 1)),
                            LOWER(SUBSTRING(
                                SUBSTRING_INDEX(
                                    SUBSTRING_INDEX(a.user_email, '@', 1),
                                    '.',
                                    -1
                                ), 2))
                        )
                    ELSE 
                        -- For emails without dots in local part (admin@test.com)
                        -- Just capitalize the first letter of the local part
                        CONCAT(
                            UPPER(SUBSTRING(SUBSTRING_INDEX(a.user_email, '@', 1), 1, 1)),
                            LOWER(SUBSTRING(SUBSTRING_INDEX(a.user_email, '@', 1), 2))
                        )
                END AS display_name,
                a.user_name,
                a.user_email,
                p.position_name,
                COUNT(*) AS total
            FROM faq_activity a
            LEFT JOIN staff_login sl ON a.user_email = sl.email
            LEFT JOIN positions p ON sl.position = p.id
            GROUP BY a.user_name, a.user_email, p.position_name
            ORDER BY total DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

/** ---------------- Ticket Models -------------------- **/
    //Get all hardware items
    public function getAllHardwareItems() {
        try {
            // Check if we need to join with categories or directly query inventory
            $sql = "SELECT id, description, serial_number, model, brand, category_id 
                    FROM inventory 
                    WHERE category_id IN (
                        SELECT id FROM categories WHERE category_name LIKE '%hardware%' 
                        OR category_name LIKE '%laptop%' 
                        OR category_name LIKE '%computer%'
                    ) 
                    OR category_id = 1"; // fallback to ID 1 if category mapping exists
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching hardware items: " . $e->getMessage());
            return [];
        }
    }

    //Create a new ticket with all fields
    public function createTicket($data)
    {
        try {
            $sql = "INSERT INTO tickets 
                    (ticket_number, user_id, subject, description, category, subcategory, priority, 
                    page_url, attachment_path, attachment_name, status, created_at) 
                    VALUES 
                    (:ticket_number, :user_id, :subject, :description, :category, :subcategory, :priority, 
                    :page_url, :attachment_path, :attachment_name, :status, :created_at)";

            $stmt = $this->db->prepare($sql);

            // Bind required fields
            $stmt->bindParam(':ticket_number', $data['ticket_number']);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':subject', $data['subject']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':subcategory', $data['subcategory']);
            $stmt->bindParam(':priority', $data['priority']);
            $stmt->bindParam(':page_url', $data['page_url']);
            $stmt->bindParam(':created_at', $data['created_at']);
            
            // Add status (default to 'open')
            $status = $data['status'] ?? 'open';
            $stmt->bindParam(':status', $status);

            // Handle optional fields
            if (!empty($data['attachment_path'])) {
                $stmt->bindParam(':attachment_path', $data['attachment_path']);
            } else {
                $stmt->bindValue(':attachment_path', null, PDO::PARAM_NULL);
            }

            if (!empty($data['attachment_name'])) {
                $stmt->bindParam(':attachment_name', $data['attachment_name']);
            } else {
                $stmt->bindValue(':attachment_name', null, PDO::PARAM_NULL);
            }

            // Execute and return inserted ID
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Ticket creation error: " . $e->getMessage());
            return false;
        }
    }

    //Generate unique ticket number with sequential format
    public function generateTicketNumber()
    {
        try {
            $year = date('Y');
            $prefix = 'TKT-' . $year . '-';
            
            // Get the last ticket number for this year
            $sql = "SELECT ticket_number FROM tickets 
                    WHERE ticket_number LIKE :pattern 
                    ORDER BY id DESC LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $pattern = $prefix . '%';
            $stmt->bindParam(':pattern', $pattern);
            $stmt->execute();
            
            $lastTicket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lastTicket) {
                $lastNumber = intval(str_replace($prefix, '', $lastTicket['ticket_number']));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
            
            return $prefix . $newNumber;
            
        } catch (PDOException $e) {
            error_log("Error generating ticket number: " . $e->getMessage());
            // Fallback to timestamp-based number
            return 'TKT-' . date('Ymd-His') . '-' . rand(100, 999);
        }
    }

    // Get user assigned inventory items
    public function getUserAssignedItems($user_id)
    {
        try {
            // Check if assigned_to column exists in inventory table
            // Based on your table structure, items are assigned to users via custodian field
            $sql = "SELECT i.id, i.serial_number, i.tag_number, i.description, 
                        i.custodian, i.location, c.category_name 
                    FROM inventory i
                    LEFT JOIN categories c ON i.category_id = c.id
                    WHERE i.custodian = :user_id 
                    ORDER BY i.description, i.serial_number";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user items: " . $e->getMessage());
            return [];
        }
    }

    // Verify user owns an inventory item
    public function verifyUserItemOwnership($user_id, $inventory_id)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM inventory 
                    WHERE custodian = :user_id 
                    AND id = :inventory_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error verifying item ownership: " . $e->getMessage());
            return false;
        }
    }

    //Get daily ticket count
    public function getDailyTicketCount()
    {
        try {
            $today = date('Y-m-d');
            $sql = "SELECT COUNT(*) as count FROM tickets WHERE DATE(created_at) = :today";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting daily ticket count: " . $e->getMessage());
            return 0;
        }
    }

    //Get ticket categories
    public function getTicketCategories()
    {
        return $this->validTicketCategories;
    }

    // Get ticket priorities
    public function getTicketPriorities()
    {
        return $this->validPriorities;
    }

    // Get ticket statuses
    public function getTicketStatuses()
    {
        return $this->validStatuses;
    }

    //Validate ticket category
    public function isValidTicketCategory($category)
    {
        return in_array($category, $this->validTicketCategories);
    }

    // Validate priority
    public function isValidPriority($priority)
    {
        return in_array($priority, $this->validPriorities);
    }

    // Validate status
    public function isValidStatus($status)
    {
        return in_array($status, $this->validStatuses);
    }

    // get ticket by ID with detailed information
    public function getTicketById($ticket_id)
    {
        try {
            $sql = "SELECT t.*, 
                           u.email as creator_email,
                           CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                           d.department_name,
                           a.email as assigned_email,
                           CONCAT(a.first_name, ' ', a.last_name) as assigned_name,
                           r.email as resolver_email,
                           CONCAT(r.first_name, ' ', r.last_name) as resolver_name
                    FROM tickets t
                    JOIN staff_login u ON t.user_id = u.id
                    LEFT JOIN departments d ON u.department = d.id
                    LEFT JOIN staff_login a ON t.assigned_to = a.id
                    LEFT JOIN staff_login r ON t.resolved_by = r.id
                    WHERE t.id = :id
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            return $ticket ?: false;
            
        } catch (PDOException $e) {
            error_log("ERROR in getTicketById: " . $e->getMessage());
            return false;
        }
    }

    // Update ticket status

    public function updateTicketStatus($ticket_id, $status, $user_id, $notes = '', $assigned_to = null)
    {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Get current status
            $currentStatus = $this->getTicketStatus($ticket_id);
            
            // Update ticket
            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($assigned_to !== null) {
                $updateData['assigned_to'] = $assigned_to;
            }
            
            if ($status === 'resolved' && $currentStatus !== 'resolved') {
                $updateData['resolved_by'] = $user_id;
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
            }
            
            $setClause = implode(', ', array_map(function($k) { 
                return "$k = :$k"; 
            }, array_keys($updateData)));
            
            $sql = "UPDATE tickets SET $setClause WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($updateData as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':id', $ticket_id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update ticket status");
            }
            
            // Log status change
            $logSql = "INSERT INTO ticket_status_log 
                      (ticket_id, old_status, new_status, changed_by, notes, created_at)
                      VALUES (:ticket_id, :old_status, :new_status, :changed_by, :notes, NOW())";
            
            $logStmt = $this->db->prepare($logSql);
            $logStmt->execute([
                ':ticket_id' => $ticket_id,
                ':old_status' => $currentStatus,
                ':new_status' => $status,
                ':changed_by' => $user_id,
                ':notes' => $notes
            ]);
            
            // Commit transaction
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating ticket status: " . $e->getMessage());
            return false;
        }
    }

    //Get ticket status
    public function getTicketStatus($ticket_id)
    {
        try {
            $sql = "SELECT status FROM tickets WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['status'] : null;
        } catch (PDOException $e) {
            error_log("Error getting ticket status: " . $e->getMessage());
            return null;
        }
    }

    //Get ticket status history
    public function getTicketStatusHistory($ticket_id)
    {
        try {
            $sql = "SELECT l.*, 
                           CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                    FROM ticket_status_log l
                    JOIN staff_login u ON l.changed_by = u.id
                    WHERE l.ticket_id = :ticket_id
                    ORDER BY l.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting status history: " . $e->getMessage());
            return [];
        }
    }

    // Submit ticket feedback
    public function submitTicketFeedback($ticket_id, $rating, $satisfaction, $comments = '')
    {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Insert feedback
            $feedbackSql = "INSERT INTO ticket_feedback 
                           (ticket_id, rating, satisfaction, comments, submitted_at)
                           VALUES (:ticket_id, :rating, :satisfaction, :comments, NOW())";
            
            $feedbackStmt = $this->db->prepare($feedbackSql);
            $feedbackStmt->execute([
                ':ticket_id' => $ticket_id,
                ':rating' => $rating,
                ':satisfaction' => $satisfaction,
                ':comments' => $comments
            ]);
            
            // Update ticket
            $updateSql = "UPDATE tickets SET feedback_submitted = 1 WHERE id = :ticket_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([':ticket_id' => $ticket_id]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error submitting feedback: " . $e->getMessage());
            return false;
        }
    }

    // Get ticket feedback
    public function getTicketFeedback($ticket_id)
    {
        try {
            $sql = "SELECT * FROM ticket_feedback WHERE ticket_id = :ticket_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting ticket feedback: " . $e->getMessage());
            return null;
        }
    }

    //Get support staff for assignment
    public function getSupportStaff()
    {
        try {
            $sql = "SELECT s.id, s.email, 
                           CONCAT(s.first_name, ' ', s.last_name) as name,
                           d.department_name
                    FROM staff_login s
                    JOIN departments d ON s.department = d.id
                    WHERE (d.department_name LIKE '%Information Systems%'
                           OR d.department_name LIKE '%Information System%'
                           OR d.department_name LIKE '%IT%'
                           OR d.department_name LIKE '%Information Technology%')
                      AND s.email IS NOT NULL
                      AND s.role IN ('admin', 'super_admin', 'staff')
                    ORDER BY s.first_name, s.last_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting support staff: " . $e->getMessage());
            return [];
        }
    }

    // Check if user can manage ticket
    public function canUserManageTicket($user_id, $ticket_id)
    {
        try {
            // Get ticket info
            $ticket = $this->getTicketById($ticket_id);
            if (!$ticket) {
                return false;
            }
            
            // If user is ticket creator
            if ($ticket['user_id'] == $user_id) {
                return true;
            }
            
            // Check if user is in support department
            $sql = "SELECT COUNT(*) as count 
                    FROM staff_login s
                    JOIN departments d ON s.department = d.id
                    WHERE s.id = :user_id
                      AND (d.department_name LIKE '%Information Systems%'
                           OR d.department_name LIKE '%Information System%'
                           OR d.department_name LIKE '%IT%'
                           OR d.department_name LIKE '%Information Technology%')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error checking ticket management permissions: " . $e->getMessage());
            return false;
        }
    }

    //Get ticket statistics
     
    public function getTicketStatistics($period = 'month')
    {
        try {
            $stats = [];
            
            switch ($period) {
                case 'day':
                    $dateCondition = "DATE(created_at) = CURDATE()";
                    break;
                case 'week':
                    $dateCondition = "YEARWEEK(created_at) = YEARWEEK(CURDATE())";
                    break;
                case 'month':
                    $dateCondition = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                    break;
                case 'year':
                    $dateCondition = "YEAR(created_at) = YEAR(CURDATE())";
                    break;
                default:
                    $dateCondition = "1=1";
            }
            
            // Total tickets
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE $dateCondition";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // By status
            $sql = "SELECT status, COUNT(*) as count 
                    FROM tickets 
                    WHERE $dateCondition 
                    GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // By category
            $sql = "SELECT category, COUNT(*) as count 
                    FROM tickets 
                    WHERE $dateCondition 
                    GROUP BY category";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // By priority
            $sql = "SELECT priority, COUNT(*) as count 
                    FROM tickets 
                    WHERE $dateCondition 
                    GROUP BY priority";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error getting ticket statistics: " . $e->getMessage());
            return [
                'total' => 0,
                'by_status' => [],
                'by_category' => [],
                'by_priority' => []
            ];
        }
    }

    // Get all tickets (for support staff/admin)
    public function getAllTickets() {
        try {
            $sql = "SELECT t.*, 
                        u.email as requester_email,
                        d.department_name,
                        a.email as assigned_email,
                        r.email as resolver_email,
                        DATE_FORMAT(t.created_at, '%M %e, %Y') as formatted_date,
                        DATE_FORMAT(t.created_at, '%l:%i %p') as formatted_time
                    FROM tickets t
                    JOIN staff_login u ON t.user_id = u.id
                    LEFT JOIN departments d ON u.department = d.id
                    LEFT JOIN staff_login a ON t.assigned_to = a.id
                    LEFT JOIN staff_login r ON t.resolved_by = r.id
                    ORDER BY t.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format names from emails
            foreach ($tickets as &$ticket) {
                $parts = explode('@', $ticket['requester_email']);
                $ticket['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                
                if (!empty($ticket['assigned_email'])) {
                    $parts = explode('@', $ticket['assigned_email']);
                    $ticket['assigned_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
                
                if (!empty($ticket['resolver_email'])) {
                    $parts = explode('@', $ticket['resolver_email']);
                    $ticket['resolver_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
            }
            
            return $tickets;
        } catch (PDOException $e) {
            error_log("Error in getAllTickets: " . $e->getMessage());
            return [];
        }
    }

    // Get tickets for specific user (regular users)
    public function getUserTickets($user_id) {
        try {
            $sql = "SELECT t.*, 
                        u.email as requester_email,
                        d.department_name,
                        a.email as assigned_email,
                        r.email as resolver_email,
                        DATE_FORMAT(t.created_at, '%M %e, %Y') as formatted_date,
                        DATE_FORMAT(t.created_at, '%l:%i %p') as formatted_time
                    FROM tickets t
                    JOIN staff_login u ON t.user_id = u.id
                    LEFT JOIN departments d ON u.department = d.id
                    LEFT JOIN staff_login a ON t.assigned_to = a.id
                    LEFT JOIN staff_login r ON t.resolved_by = r.id
                    WHERE t.user_id = ?
                    ORDER BY t.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format names from emails
            foreach ($tickets as &$ticket) {
                $parts = explode('@', $ticket['requester_email']);
                $ticket['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                
                if (!empty($ticket['assigned_email'])) {
                    $parts = explode('@', $ticket['assigned_email']);
                    $ticket['assigned_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
                
                if (!empty($ticket['resolver_email'])) {
                    $parts = explode('@', $ticket['resolver_email']);
                    $ticket['resolver_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
            }
            
            return $tickets;
        } catch (PDOException $e) {
            error_log("Error in getUserTickets: " . $e->getMessage());
            return [];
        }
    }

    // Quick assign ticket AJAX
    public function quickAssignTicket($ticket_id, $assigned_to) {
        try {
            $sql = "UPDATE tickets SET 
                    assigned_to = :assigned_to, 
                    updated_at = NOW() 
                    WHERE id = :ticket_id";
            
            $stmt = $this->db->prepare($sql);
            
            // Handle null assignment
            if ($assigned_to === null || $assigned_to === '' || $assigned_to === 'null') {
                $stmt->bindValue(':assigned_to', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in quickAssignTicket: " . $e->getMessage());
            return false;
        }
    }

    // Get status history with formatted dates for AJAX
    public function getFormattedStatusHistory($ticket_id) {
        try {
            $sql = "SELECT l.*, 
                        CONCAT(u.first_name, ' ', u.last_name) as changed_by_name,
                        DATE_FORMAT(l.created_at, '%M %e, %Y %l:%i %p') as formatted_date
                    FROM ticket_status_log l
                    JOIN staff_login u ON l.changed_by = u.id
                    WHERE l.ticket_id = :ticket_id
                    ORDER BY l.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format names from emails if first_name/last_name not available
            foreach ($results as &$row) {
                if (empty($row['changed_by_name']) || trim($row['changed_by_name']) === '') {
                    // Try to get email and format it
                    $emailSql = "SELECT email FROM staff_login WHERE id = ?";
                    $emailStmt = $this->db->prepare($emailSql);
                    $emailStmt->execute([$row['changed_by']]);
                    $emailData = $emailStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($emailData && !empty($emailData['email'])) {
                        $parts = explode('@', $emailData['email']);
                        $row['changed_by_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                    } else {
                        $row['changed_by_name'] = 'System';
                    }
                }
                
                // Ensure formatted_date exists
                if (empty($row['formatted_date'])) {
                    $row['formatted_date'] = date('F j, Y g:i A', strtotime($row['created_at']));
                }
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getFormattedStatusHistory: " . $e->getMessage());
            return [];
        }
    }

    // Get ticket count by status
    public function getTicketCountByStatus($status, $user_id = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM tickets WHERE status = :status";
            $params = [':status' => $status];
            
            if ($user_id) {
                $sql .= " AND user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error in getTicketCountByStatus: " . $e->getMessage());
            return 0;
        }
    }


        /** ---------------- CONSUMABLES INVENTORY TRACKING MODULE -------------------- **/

    // getting users for issuer and receiver
    public function getAllStaffNames()
    {
        $sql = "SELECT id, email FROM staff_login ORDER BY email";
        $query = $this->db->prepare($sql);
        $query->execute();
        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as &$user) {
            $namePart = explode('@', $user['email'])[0];
            $user['name'] = ucwords(str_replace('.', ' ', $namePart));
        }

        return $users; // now each user has ['id'=>..., 'email'=>..., 'name'=>...]
    }


    //consumable items (CRUD)
    public function getAllConsumableItems() {
        $sql = "SELECT * FROM consumable_items ORDER BY item_name ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function getConsumableItem($id) {
        $sql = "SELECT * FROM consumable_items WHERE id = :id LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function createConsumableItem($data) 
    {
        $sql = "INSERT INTO consumable_items (item_name, item_code, unit, reorder_level, expiry_date) 
                VALUES (:item_name, :item_code, :unit, :reorder_level, :expiry_date)";
        $query = $this->db->prepare($sql);
        $query->bindParam(':item_name', $data['item_name'], PDO::PARAM_STR);
        $query->bindParam(':item_code', $data['item_code'], PDO::PARAM_STR);
        $query->bindParam(':unit', $data['unit'], PDO::PARAM_STR);
        $query->bindParam(':reorder_level', $data['reorder_level'], PDO::PARAM_INT);
        $query->bindParam(':expiry_date', $data['expiry_date'], PDO::PARAM_STR);
        return $query->execute();
    }

    public function updateConsumableItem($id, $data)
    {
        $sql = "UPDATE consumable_items 
                SET item_name=:item_name, item_code=:item_code, unit=:unit, 
                    reorder_level=:reorder_level, expiry_date=:expiry_date
                WHERE id=:id";
        $query = $this->db->prepare($sql);
        $query->bindParam(':item_name', $data['item_name'], PDO::PARAM_STR);
        $query->bindParam(':item_code', $data['item_code'], PDO::PARAM_STR);
        $query->bindParam(':unit', $data['unit'], PDO::PARAM_STR);
        $query->bindParam(':reorder_level', $data['reorder_level'], PDO::PARAM_INT);
        $query->bindParam(':expiry_date', $data['expiry_date'], PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    //consumable items tracking (CRUD)
    public function getConsumableTransactions($item_id) {
        $sql = "SELECT * FROM consumable_transactions WHERE item_id = :item_id ORDER BY transaction_date ASC";
        $query = $this->db->prepare($sql);
        $query->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function createConsumableTransaction($data) {
        $sql = "INSERT INTO consumable_transactions 
                (item_id, transaction_type, quantity, transaction_date, receiver_name, issuer_name, created_by)
                VALUES (:item_id, :transaction_type, :quantity, :transaction_date, :receiver_name, :issuer_name, :created_by)";
        $query = $this->db->prepare($sql);
        $query->bindParam(':item_id', $data['item_id'], PDO::PARAM_INT);
        $query->bindParam(':transaction_type', $data['transaction_type'], PDO::PARAM_STR);
        $query->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
        $query->bindParam(':transaction_date', $data['transaction_date'], PDO::PARAM_STR);
        $query->bindParam(':receiver_name', $data['receiver_name'], PDO::PARAM_STR);
        $query->bindParam(':issuer_name', $data['issuer_name'], PDO::PARAM_STR);
        $query->bindParam(':created_by', $data['created_by'], PDO::PARAM_INT);
        return $query->execute();
    }

    public function getConsumableBalance($item_id) {
        // Total receipts
        $sqlIn = "SELECT SUM(quantity) as total FROM consumable_transactions WHERE item_id = :item_id AND transaction_type = 'receipt'";
        $queryIn = $this->db->prepare($sqlIn);
        $queryIn->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $queryIn->execute();
        $total_in = $queryIn->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // Total issues
        $sqlOut = "SELECT SUM(quantity) as total FROM consumable_transactions WHERE item_id = :item_id AND transaction_type = 'issue'";
        $queryOut = $this->db->prepare($sqlOut);
        $queryOut->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $queryOut->execute();
        $total_out = $queryOut->fetch(PDO::FETCH_OBJ)->total ?? 0;

        return $total_in - $total_out;
    }
    //etting the quatery report
    public function getQuarterlySummary($year = null)
    {
        if (!$year) {
            $year = date('Y'); // default to current year
        }

        $sql = "
            SELECT 
                ci.id AS item_id,
                ci.item_name,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 1 AND ct.transaction_type='receipt' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q1_received,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 1 AND ct.transaction_type='issue' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q1_issued,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 2 AND ct.transaction_type='receipt' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q2_received,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 2 AND ct.transaction_type='issue' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q2_issued,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 3 AND ct.transaction_type='receipt' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q3_received,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 3 AND ct.transaction_type='issue' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q3_issued,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 4 AND ct.transaction_type='receipt' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q4_received,
                SUM(CASE WHEN QUARTER(ct.transaction_date) = 4 AND ct.transaction_type='issue' AND YEAR(ct.transaction_date) = :year THEN ct.quantity ELSE 0 END) AS Q4_issued
            FROM consumable_items ci
            LEFT JOIN consumable_transactions ct ON ci.id = ct.item_id
            GROUP BY ci.id, ci.item_name
            ORDER BY ci.item_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add current balance for each item
        foreach ($items as &$item) {
            $item['current_balance'] = $this->getConsumableBalance($item['item_id']);
        }

        return $items;
    }

    //fetching expiring items within 3 months
    public function getExpiringItems()
    {
        $sql = "SELECT *
                FROM consumable_items
                WHERE expiry_date IS NOT NULL
                AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
                AND expiry_date >= CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        /** ---------------- GEAR INVENTORY TRACKING MODULE -------------------- **/

        // Add new gear to inventory
    public function addInventoryItem($item_name, $number_procured, $comments = '') {
        $in_store = $number_procured;
        $stmt = $this->db->prepare("
            INSERT INTO gear_inventory (item_name, number_procured, number_issued, in_store, comments)
            VALUES (?, ?, 0, ?, ?)
        ");
        return $stmt->execute([$item_name, $number_procured, $in_store, $comments]);
    }

        // Get allgear inventory items
    public function getAllInventory() {
        $stmt = $this->db->query("SELECT * FROM gear_inventory ORDER BY item_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        // Get a single gear item by ID
    public function getInventoryById($id) {
        $stmt = $this->db->prepare("SELECT * FROM gear_inventory WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    // Update gear inventory item counts or comments
    public function updateInventoryItem($id, $number_procured, $number_issued, $comments = '') {
        $in_store = $number_procured - $number_issued;
        $stmt = $this->db->prepare("
            UPDATE gear_inventory
            SET number_procured = ?, number_issued = ?, in_store = ?, comments = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$number_procured, $number_issued, $in_store, $comments, $id]);
    }

        // Delete inventory item
    public function deleteInventoryItem($id) {
        $stmt = $this->db->prepare("DELETE FROM gear_inventory WHERE id = ?");
        return $stmt->execute([$id]);
    }

        // Issue gear to staff
    public function issueGear($staff_id, $item_id, $quantity, $date_issued, $item_condition = 'Good')
    {
        $sql = "INSERT INTO gear_items_tracking (staff_id, item_id, date_issued, quantity, item_condition)
                VALUES (:staff_id, :item_id, :date_issued, :quantity, :item_condition)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':staff_id' => $staff_id,
            ':item_id' => $item_id,
            ':date_issued' => $date_issued,
            ':quantity' => $quantity,
            ':item_condition' => $item_condition
        ]);

        // Update gear inventory counts
        $update = "UPDATE gear_inventory SET
                    number_issued = number_issued + :qty,
                    in_store = number_procured - (number_issued + :qty)
                WHERE id = :item_id";
        $stmt2 = $this->db->prepare($update);
        $stmt2->execute([':qty' => $quantity, ':item_id' => $item_id]);
    }

        // Get all gear issued
    public function getAllIssuedGear()
    {
        $sql = "
            SELECT 
                t.*, 
                i.item_name,
                s.email
            FROM gear_items_tracking t
            JOIN gear_inventory i ON t.item_id = i.id
            JOIN staff_login s ON t.staff_id = s.id
            ORDER BY t.date_issued DESC
        ";

        $stmt = $this->db->query($sql);
        $issuedGear = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($issuedGear as &$gear) {
            $namePart = explode('@', $gear['email'])[0];
            $gear['staff_name'] = ucwords(str_replace('.', ' ', $namePart));
        }

        return $issuedGear;
    }

        // Get issued gear per staff
    public function getIssuedByStaff($staff_id) {
        $stmt = $this->db->prepare("
            SELECT t.id, i.item_name, t.quantity, t.date_issued, t.item_condition, t.signed_by
            FROM gear_items_tracking t
            JOIN gear_inventory i ON t.item_id = i.id
            WHERE t.staff_id = ?
            ORDER BY t.date_issued DESC
        ");
        $stmt->execute([$staff_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        // Update issued gear record
    public function updateIssuedGear($id, $quantity, $item_condition = 'Good', $signed_by = '') {
        // Fetch current record to adjust inventory counts
        $stmt = $this->db->prepare("SELECT item_id, quantity FROM gear_items_tracking WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) return false;

        $item_id = $record['item_id'];
        $old_quantity = $record['quantity'];
        $diff = $quantity - $old_quantity;

        $stmt2 = $this->db->prepare("
            UPDATE gear_items_tracking
            SET quantity = ?, item_condition = ?, signed_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $success = $stmt2->execute([$quantity, $item_condition, $signed_by, $id]);

        if ($success && $diff != 0) {
            // Adjust inventory counts
            $stmt3 = $this->db->prepare("
                UPDATE gear_inventory
                SET number_issued = number_issued + ?, in_store = number_procured - (number_issued + ?), updated_at = NOW()
                WHERE id = ?
            ");
            if ($diff > 0) {
                $stmt3->execute([$diff, $diff, $item_id]);
            } else {
                $stmt3->execute([$diff, abs($diff), $item_id]);
            }
        }

        return $success;
    }

        // Delete issued gear (returning to inventory)
    public function deleteIssuedGear($id) {
        $stmt = $this->db->prepare("SELECT item_id, quantity FROM gear_items_tracking WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) return false;

        $item_id = $record['item_id'];
        $quantity = $record['quantity'];

        $stmt2 = $this->db->prepare("DELETE FROM gear_items_tracking WHERE id = ?");
        $success = $stmt2->execute([$id]);

        if ($success) {
            // Adjust inventory
            $stmt3 = $this->db->prepare("
                UPDATE gear_inventory
                SET number_issued = number_issued - ?, in_store = number_procured - (number_issued - ?), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt3->execute([$quantity, $quantity, $item_id]);
        }

        return $success;
    }

    public function getStaffWithIssuedGear()
    {
        $sql = "
            SELECT 
                s.id,
                s.email,
                CONCAT(loc.location_name, ' - ', o.office_name) AS duty_station,
                COUNT(t.id) AS total_items
            FROM gear_items_tracking t
            JOIN staff_login s ON t.staff_id = s.id
            LEFT JOIN offices o ON s.dutystation = o.id
            LEFT JOIN locations loc ON o.location_id = loc.id
            GROUP BY s.id
            ORDER BY s.email ASC
        ";

        $stmt = $this->db->query($sql);
        $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert email to display name
        foreach ($staffList as &$staff) {
            $namePart = explode('@', $staff['email'])[0];
            $staff['staff_name'] = ucwords(str_replace('.', ' ', $namePart));
        }

        return $staffList;
    }

    /** ---------------- NON-CONSUMABLES INVENTORY TRACKING MODULE -------------------- **/
    // SERIALISED NONCONSUMABLES FUNCTIONS
    public function addSerialised($data)
    {
        $sql = "INSERT INTO nonconsumables_serialized
                (item_name, description, serial_number, specification, accessories,
                quantity_received, quantity_in_store, date_received,
                current_status, condition_status, recipient_name, remarks)
                VALUES
                (:item_name, :description, :serial_number, :specification, :accessories,
                :quantity_received, :quantity_in_store, :date_received,
                :current_status, :condition_status, :recipient_name, :remarks)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }


    public function getAllSerialised()
    {
        try {
            $sql = "SELECT ns.* 
                    FROM nonconsumables_serialized ns
                    ORDER BY ns.item_name ASC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Error fetching serialized non-consumables: " . $e->getMessage();
            return [];
        }
    }

    public function getSerialisedById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM nonconsumables_serialized WHERE id = :id"
        );

        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSerialised($data) 
    {
        $sql = "UPDATE nonconsumables_serialized SET
                    item_name = :item_name,
                    description = :description,
                    serial_number = :serial_number,
                    specification = :specification,
                    accessories = :accessories,
                    quantity_received = :quantity_received,
                    quantity_in_store = :quantity_in_store,
                    date_received = :date_received,
                    current_status = :current_status,
                    condition_status = :condition_status,
                    recipient_name = :recipient_name,
                    remarks = :remarks
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }


    public function deleteSerialised($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM nonconsumables_serialized WHERE id = :id"
        );

        return $stmt->execute([':id' => $id]);
    }

    public function addSerialisedMovement($data)
    {
        $sql = "INSERT INTO nonconsumables_serialized_movements
                (serialized_id, movement_type, movement_date,
                destination, remarks, recorded_by)
                VALUES
                (:serialized_id, :movement_type, :movement_date,
                :destination, :remarks, :recorded_by)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function getSerialisedMovements($serialized_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM nonconsumables_serialized_movements
            WHERE serialized_id = :id
            ORDER BY movement_date DESC"
        );

        $stmt->execute([':id' => $serialized_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addSerialisedConditionLog($data)
    {
        $sql = "INSERT INTO nonconsumables_serialized_condition_log
                (serialized_id, condition_status, remarks, checked_by, date_checked)
                VALUES
                (:serialized_id, :condition_status, :remarks, :checked_by, :date_checked)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':serialized_id'   => $data['serialized_id'],
            ':condition_status'=> $data['condition_status'],
            ':remarks'         => $data['remarks'] ?? null,
            ':checked_by'      => $data['checked_by'] ?? null,
            ':date_checked'    => $data['date_checked']
        ]);
    }

    // Optional: Fetch logs for a serialized item
    public function getConditionLogs($serialized_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM nonconsumables_serialized_condition_log
            WHERE serialized_id = :id
            ORDER BY date_checked DESC"
        );
        $stmt->execute([':id' => $serialized_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // BULK NONCONSUMABLES FUNCTIONS
    // Add Bulk Item
    public function addBulk($data)
    {
        $sql = "INSERT INTO nonconsumables_bulk
                (item_name, specification, description, condition_status, last_checked_date, remarks)
                VALUES
                (:item_name, :specification, :description, :condition_status, :last_checked_date, :remarks)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Get all bulk items
    public function getAllBulk()
    {
        $sql = "SELECT *
                FROM nonconsumables_bulk
                ORDER BY created_at DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getBulkById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM nonconsumables_bulk WHERE id = :id"
        );

        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBulk($id, $data)
    {
        $sql = "UPDATE nonconsumables_bulk SET
                    item_name = :item_name,
                    specification = :specification,
                    description = :description,
                    condition_status = :condition_status,
                    last_checked_date = :last_checked_date,
                    remarks = :remarks
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        // Add the ID to the data array for the WHERE clause
        $data['id'] = $id;

        return $stmt->execute($data);
    }


    public function deleteBulk($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM nonconsumables_bulk WHERE id = :id"
        );

        return $stmt->execute([':id' => $id]);
    }

    // Add a new bulk movement and update totals
    public function addBulkMovement($data)
    {
        // 1️⃣ Insert into movements table
        $sql = "INSERT INTO nonconsumables_bulk_movements
                (bulk_id, movement_type, quantity, movement_date, destination, remarks, recorded_by)
                VALUES
                (:bulk_id, :movement_type, :quantity, :movement_date, :destination, :remarks, :recorded_by)";
        $stmt = $this->db->prepare($sql);
        $inserted = $stmt->execute($data);

        if (!$inserted) return false;

        // 2️⃣ Update the totals in the bulk table
        $columnMap = [
            'received'   => 'total_received',
            'dispatched' => 'total_dispatched',
            'returned'   => 'total_returned',
            'disposed'   => 'total_disposed'
        ];

        if (isset($columnMap[$data['movement_type']])) {
            $column = $columnMap[$data['movement_type']];
            $sqlUpdate = "UPDATE nonconsumables_bulk
                        SET $column = $column + :quantity
                        WHERE id = :bulk_id";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'quantity' => $data['quantity'],
                'bulk_id'  => $data['bulk_id']
            ]);
        }

        return true;
    }
    // Get all movements for a specific bulk item
    public function getBulkMovements($bulk_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM nonconsumables_bulk_movements
            WHERE bulk_id = :id
            ORDER BY movement_date DESC"
        );

        $stmt->execute([':id' => $bulk_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }






}
          



