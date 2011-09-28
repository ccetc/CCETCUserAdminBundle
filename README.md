UserAdminBundle
=========

Provides an a SonataAdmin class for managing users.
Used on all front and back ends of CCETC web applications.

## Installation
To install as a Symfony vendor, add the following lines to the file ``deps``:

        [ErrorReportBundle]
                git=https://github.com/CCETC/UserAdminBundle.git
                target=/bundles/CCETC/UserAdminBundle


If you are using git, you can instead add them as submodules:

        git submodule add git@github.com:CCETC/UserAdminBundle.git vendor/bundles/CCETC/UserAdminBundle


Unpack the Admin class:

        cd vendors/bundles/CCETC/UserAdminBundle
        cp Admin/UserAdmin.php.dist Admin/UserAdmin.php
        
        
Add your user entity to Admin/UserAdmin.php:

        // Add our user entity here:
        use Path\To\Your\User\Entity as User;


Add service to app/config.yml:

        ccetc.useradmin.admin.user:
                class: CCETC\UserAdminBundle\Admin\UserAdmin
                tags:
                        - { name: sonata.admin, manager_type: orm, group: User Data, label: Users }
                arguments: [null, Path\To\Your\User\Entity, CCETCUserAdminBundle:UserAdmin]

Install assets:
        
        bin/vendors install

## Customization
You can customize UserAdmin.php to your needs.

TODO: document customization