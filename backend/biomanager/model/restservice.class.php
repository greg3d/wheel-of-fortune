<?php 
require_once MODX_CORE_PATH . 'model/modx/rest/modrestservice.class.php';

class restService extends modRestService
{

    /**
     * Check permissions for the request.
     *
     * @return boolean
     */
    public function checkPermissions(): bool
    {
        // Здесь позже напишем логику проверки авторизации и прав
        if ($this->modx->user->isAuthenticated('web') || $this->modx->user->isAuthenticated('mgr')) {
            return true;
        }
        return true;
        // TODO remove true

    }
    
}