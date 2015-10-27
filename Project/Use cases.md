#Requirement specifications

##Shared family & friends space with checklists etc.

#UC1.1 Register a new user
##Main scenario
 1. Starts when a user wants to register
 2. System asks for username and password
 3. User provides details
 4. System checks details, registers (saves) the new user and navigates to a login page

## Alternate Scenarios
 * 4a. User details was faulty (for example already taken username, to short username etc)
   1. System presents an error message
   2. Step 2 in main scenario

#UC1.2 Authenticate user
##Preconditions
A user has registered (UC1.1).
##Main scenario
 1. Starts when a user wants to authenticate (is on a login page).
 2. System asks for username, password, and if system should save the user credentials
 3. User provides username and password
 4. System authenticates the user and redirects to home page

## Alternate Scenarios
 * 3a. User wants the system to keep user credentials for easier login.
   * 1. The system authenticates the user and presents that the authentication succeeded and that the user credentials was saved.
 * 4a. User could not be authenticated
   1. System presents an error message
   2. Step 2 in main scenario

#UC2.1 Creating a user group
##Preconditions
A user is authenticated (UC1.2).
##Main scenario
 1. Starts when a user goes to the page to create a new user group.
 2. System asks for details (a name).
 3. User provides details.
 4. System checks the details, saves the user group and presents the user groups's page.

#UC2.2 Viewing a user group
##Preconditions
A user is authenticated (UC1.2) and a group has been created (UC2.1).
##Main scenario
 1. Starts when a user navigates to a user group page.
 2. System retrieves the user group details and checks if user has access (is creator or member of the group).

##Alternate scenarios
 * 2a. User has no access to the user group. 
   * 1. System presents an error message. 

#UC2.3 Adding a new member to a user group
##Preconditions
A user is authenticated (UC1.2), a second user has been registered (UC1.1) and the logged in user has access to a user group (UC2.2).
##Main scenario
 1. Starts when a user has navigated to a user group which he has access to.
 2. System asks for a username for the member to add.
 3. User provides details.
 4. System searches for the member and presents it.
 5. User chooses to add the found member.
 6. System saves the member to the user group and presents the user groups's page with the added member in the list.

#UC3.1 Creating a checklist
##Preconditions
A user is authenticated (UC1.2).
##Main scenario
 1. Starts when a user navigates to the page to create a new checklist.
 2. System asks for details (title and description).
 3. User provides details.
 4. System checks the details, saves the checklist and navigates to the checklist's page.

#UC3.2 Viewing a checklist
##Preconditions
A user is authenticated (UC1.2) and a checklist has been created (UC3.1).
##Main scenario
 1. Starts when a user navigates to a checklist page.
 2. System retrieves the checklist details and checks if user has access (is creator or is a member of a group that has been given access).

##Alternate scenarios
 * 2a. User has no access to the checklist. 
   * 1. System presents an error message. 

#UC3.3 Adding a new checklist item to a checklist
##Preconditions
A user is authenticated (UC1.2) and has access to a checklist (UC3.2).
##Main scenario
 1. Starts when a user has navigated to a checklist which he has access to.
 2. System asks for details (title, description, and if it is important or not)
 3. User provides details.
 4. System checks the details, saves the checklist item and presents the updated checklist's page.

##Alternate scenarios
 * 2a. User has provided faulty details.
   * 1. System presents an error message. 
   * 2. Step 2 in main scenario.

#UC3.4 Setting a new state to a checklist item in a checklist
##Preconditions
A user is authenticated (UC1.2) and has access to a checklist (UC3.2) and a checklist item has been created (UC3.3).
##Main scenario
 1. Starts when a user has navigated to a checklist which he has access to.
 2. User changes the state for a checklist item (for example checks an unchecked item). 
 3. System saves the new state and presents the updated checklist's page.

#UC4.1 Sharing an entry (like a checklist) with a user group
##Preconditions
A user is authenticated (UC1.2), is member/creator of a user group (ex UC2.1) and has access to an entry (ex checklist UC3.2).
##Main scenario
 1. Starts when a user has navigated to an entry which he has access to.
 2. User chooses a user group to add access for to the entry.
 3. System stores the user group access for the entry and presents the updated entry's page.
