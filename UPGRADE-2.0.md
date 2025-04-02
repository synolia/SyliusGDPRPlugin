# UPGRADE FROM `v1.X` TO `v2.0`

* For each implementation of `Synolia\SyliusGDPRPlugin\Loader\LoaderInterface`, add the `getDefaultPriority` static function.
* Use attribute instead of annotation.
* The service tag `anonymization_loader` has been removed. Use `Synolia\SyliusGDPRPlugin\Loader\LoaderInterface` instead.
