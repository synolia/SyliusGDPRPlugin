# UPGRADE FROM `v1.1.X` TO `v1.2.0`

Replace the actual route configuration:

Before
  ```yaml
  synolia_gdpr:
      resource: "@SynoliaSyliusGDPRPlugin/Resources/config/routes/admin/customer.yaml"
      prefix: '/%sylius_admin.path_name%'
  ```

After
  ```yaml
  synolia_gdpr:
      resource: "@SynoliaSyliusGDPRPlugin/Resources/config/routes.yaml"
      prefix: '/%sylius_admin.path_name%'
  ```
