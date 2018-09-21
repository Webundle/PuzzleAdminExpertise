# Puzzle Client Expertise Bundle
**=========================**

Puzzle bundle for managing admin 

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

`composer require webundle/puzzle-admin-expertise`

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
{
    $bundles = array(
    // ...

    new Puzzle\Admin\ExpertiseBundle\PuzzleAdminExpertiseBundle(),
                    );

 // ...
}

 // ...
}
```

### Step 3: Register the Routes

Load the bundle's routing definition in the application (usually in the `app/config/routing.yml` file):

# app/config/routing.yml
```yaml
puzzle_admin_expertise:
        resource: "@PuzzleAdminExpertiseBundle/Resources/config/routing.yml"
```

### Step 4: Configure Dependency Injection

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Puzzle Admin Expertise
puzzle_admin_expertise:
    title: expertise.title
    description: expertise.description
    icon: expertise.icon
    roles:
        default:
            label: 'ROLE_EXPERTISE'
            description: expertise.role.default
```

### Step 5: Enable module

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Client Admin
puzzle_admin:
    ...
    navigation:
    	nodes:
    		expertise:
                label: 'expertise.base'
                translation_domain: 'admin'
                attr:
                    class: 'icon-briefcase3'
                parent: ~
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.tooltip'
            expertise_service:
                label: 'expertise.service.base'
                translation_domain: 'admin'
                path: 'admin_expertise_service_list'
                sub_paths: ['admin_expertise_service_create', 'admin_expertise_service_update', 'admin_expertise_service_show']
                parent: expertise
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.service.tooltip'
            expertise_project:
                label: 'expertise.project.base'
                translation_domain: 'admin'
                path: 'admin_expertise_project_list'
                sub_paths: ['admin_expertise_project_create', 'admin_expertise_project_update', 'admin_expertise_project_show']
                parent: expertise
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.project.tooltip'
            expertise_partner:
                label: 'expertise.partner.base'
                translation_domain: 'admin'
                path: 'admin_expertise_partner_list'
                parent: expertise
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.partner.tooltip'
            expertise_staff:
                label: 'expertise.staff.base'
                translation_domain: 'admin'
                path: 'admin_expertise_staff_list'
                sub_paths: ['admin_expertise_staff_create', 'admin_expertise_staff_update', 'admin_expertise_staff_show']
                parent: expertise
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.staff.tooltip'
            expertise_testimonial:
                label: 'expertise.testimonial.base'
                translation_domain: 'admin'
                path: 'admin_expertise_testimonial_list'
                sub_paths: ['admin_expertise_testimonial_create', 'admin_expertise_testimonial_update', 'admin_expertise_testimonial_show']
                parent: expertise
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.testimonial.tooltip'
            expertise_faq:
                label: 'expertise.faq.base'
                translation_domain: 'admin'
                path: 'admin_expertise_faq_list'
                sub_paths: ['admin_expertise_faq_create', 'admin_expertise_faq_update', 'admin_expertise_faq_show']
                parent: expertise
                user_roles: ['ROLE_EXPERTISE', 'ROLE_ADMIN']
                tooltip: 'expertise.faq.tooltip'
```