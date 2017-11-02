# REDCap User Profile
REDCap User Profile is an external module that extends user accounts information according to your needs - e.g. address, country of birth, job position, etc. This module provides:
- An easy way to manage user profiles
- An API to assist developers in accessing user profiles information

## How does it work?
This module assigns to a REDCap project the role of representing and storing user profile entities as data entry records.

## Prerequisites
- [REDCap Modules](https://github.com/vanderbilt/redcap-external-modules)

## Installation
- Clone this repo into to `<redcap-root>/modules/redcap_user_profile_v1.0`.
- Go to **Control Center > Manage External Modules** and enable User Profile for all modules.

## Configuration

### Making sure Table-based authentication is enabled
Make sure that Table-based authentication is enabled in your REDCap, since it is required to manage user accounts.

### Create an User Profile project
Create a REDCap project in order to extend user information according to your needs - e.g. address, country of birth, job position, etc. **Make sure to create a field that represents REDCap username** - that's how user accounts and profiles are connected.

### Filling the settings form
Go to **Control Center > Manage External Modules**, click on User Profile's **Configure** button, and fill the form as follows:
  - **Project**: The project you created
  - **Username field**: The key of username field you created

## Managing User Profiles
You can manage user profiles in two ways.

### Option 1
Directly on User Profile project - creating new records, making sure to associate a REDCap username for each profile.

### Option 2
By accessing user account page (go to **Control Manager > Browse Users** and click on **View Users** to choose the account). 

There, you will be able to see a **Create User Profile** button or an **Edit User Profile** button. Either button will redirect you to the user profile form - for new profiles, the username field will be automatically prefilled.

## API usage
User Profile module provides `UserProfile` class. Here is an example of usage:

```
use UserProfile\UserProfile;

$profile = new UserProfile('test_username');
$data = $profile->getProfileData();

// Assuming your User Profile project contains an address field which key is 'street_address'.
$address = $data['street_address'];
```

Here are other methods that might be useful:
```
// Gets profile record ID.
$profile->getProfileId();

// Gets username.
$profile->getUsername();

// Gets username field key.
$profile->getUsernameField();

// Gets user profile project ID.
$profile->getProjectId();
```

There is also a static method to get all available profiles.
```
use UserProfile\UserProfile;

$profiles = UserProfiles::getProfiles();

// Creating an array of addresses, keyed by username.
$addresses = array();
foreach ($profiles as $username => $profile) {
    $data = $profile->getData();
    $addresses[$username] = $data['street_address'];
}
```
