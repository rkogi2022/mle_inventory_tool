CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`) VALUES
(1, 'How do you reset your password', 'On the login page, click the \"Reset Password\" and it redirects you to a page where you can create a new password and once done you are redirected to the login page again'),
(2, 'How do you acknowledge a new assignment', 'Click on the \"Pending Assignments\" page and this will direct you to your pending assignments. Under actions, add the state of the device, then click the \'Acknowledge\' button'),
(3, 'How do you return an item assigned', 'On \"My Items\" page under \"Assigned Items\", click on the \'Record Return\' button. This will open a modal pop-up, select the item or items you want to return. Click next add it will take you to the return date, which populates with todays date, then click next. You will then be prompted to choose who is the receiver then submit.'),
(4, 'How do I track a returned item', 'Once you click \"My Items\" then \"Returned Items\", it will open a page with returned items list. On the status column, you can track the status of your item.'),
(5, 'How do I view assignments given to my team', 'On the \"Reports Module\", click on \"Staff Assignments\", you will be able to view all items assigned to staff who directly report to you. You can also see if they have received the items or not and follow up in case a follow up is needed.'),
(6, 'How do I view assignments returned by my team', 'On the \"Reports Module\", click on \"Staff Returned Items\", you will be able to view all items returned by staff who directly report to you. You can also see if the items have been officially collected or not and follow up in case a follow up is needed.'),
(7, 'How do you assign a new item', 'Option 1: On the \"Assignments\" page, there is a button \"Assign New Item\" that takes you to a new page. Select the item or items you want to assign, assign the user, date of the assignment and the manager to the user and submit.\r\nOption 2:On the \"Inventory\" submodule under \"Configurations\" module, there is a list of all the inventory recorded. On every specific item, there is an \'Assign\' button, on the \"Actions\" column. Once you click it, you are able to select the user, the user\'s manager and the date the item is  being assigned.'),
(8, 'How to edit an assignment', 'On the \"Assignments\" page, under the Action column, there is an \'Edit button\'. Upon clicking it, you can make desired updates. NB: In the instance the user has acknowledged the item, you can not edit their assignment.'),
(9, 'How do you delete an assigned item', 'On the \"Assignments\" page, under the Action column, there is an \'Delete button\'. Upon clicking it, you can make desired updates. NB: In the instance the user has acknowledged the item, you can not edit their assignment.'),
(10, 'How to approve a returned item', 'Click \"Collections\", the \"Pending Approvals\", this will open a list of item that you were marked as the item receiver. Under the Actions column, choose the state of the item ie functional, damaged, lost or even disapproved and then submit'),
(11, 'How do I mark an item as repaired or unrepairable', 'On the \"Collections\" dropdown, click \"Repairs\" option. This will open a list of damaged items. For an item whose repair status is pending, choose the state of the item ie repaired or retired. If the item, is repaired and is in a good state to use, it goes back to the \"Assets\" module under \'In-Stock\' sub-module, ready to be assigned to a user. If the item is retired, it remains on the damaged items list.'),
(12, 'How to view assigned items/inventory', 'Go to the \'My Items\' section on the navbar and a dropdown will populate, choose “Assigned Items”. You will see a list of all items assigned to you.'),
(13, 'How to initiate re-confirmation of assigned items', 'On the \"Configurations\" module, under \'Users\', there is a toggle button, \"Re-Confirm\". Click on the button to initiate the process. This will send emails to all the users, requesting them to reconfirm the items in their possession. In the instance a user doesn\'t confirm their items, a reminder email is sent after 5 working days.'),
(14, 'How do I re-confirm my item/ items?', 'Click \"Assigned Items\" page under \' My Items\' module. This opens a list of items assigned to you. On the Action column, there is a green button \"Confirm\", when you confirm, the button becomes disabled, hence turning grey.'),
(15, 'How do I view the reconfirmation reports', 'The reports are available on the Assets module, under \'Confirmation Reports\' sub-module. These reports allow you to filter the reports per period i.e., the year and the month the re-confirmation was initiated. You can also use the search field on the table to filter pending and confirmed items.');


CREATE TABLE faq_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,      -- store email
    user_name VARCHAR(50) DEFAULT NULL,    -- store derived name
    searched_text VARCHAR(255) DEFAULT NULL,
    faq_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
