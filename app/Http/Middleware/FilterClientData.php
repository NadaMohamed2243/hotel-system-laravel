<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterClientData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only process Inertia responses
        if (!method_exists($response, 'getData')) {
            return $response;
        }
        
        $data = $response->getData();
        
        // Check if we have client data to filter
        if (!isset($data['props']['clients'])) {
            return $response;
        }
        
        $user = $request->user();
        
        // Keep all data for admins and managers who can view full client details
        if ($user && $user->hasPermissionTo('view client details')) {
            return $response;
        }
        
        // For receptionists with limited access, filter out sensitive data
        if ($user && $user->hasRole('receptionist')) {
            $clients = $data['props']['clients'];
            
            if (is_array($clients)) {
                // Filter collection of clients
                foreach ($clients as &$client) {
                    $this->filterSensitiveData($client);
                }
            } elseif (is_object($clients) && property_exists($clients, 'data')) {
                // Filter paginated clients
                foreach ($clients->data as &$client) {
                    $this->filterSensitiveData($client);
                }
            } elseif (is_object($clients)) {
                // Filter single client
                $this->filterSensitiveData($clients);
            }
            
            $data['props']['clients'] = $clients;
            $response->setData($data);
        }
        
        return $response;
    }
    
    /**
     * Filter sensitive data from a client object
     */
    private function filterSensitiveData(&$client): void
    {
        // Remove sensitive fields that receptionists shouldn't see
        $sensitiveFields = [
            'national_id',
            'payment_details',
            'credit_card',
            'financial_info',
            'discount_info',
            'special_rates',
            // Add other sensitive fields here
        ];
        
        foreach ($sensitiveFields as $field) {
            if (is_array($client) && isset($client[$field])) {
                unset($client[$field]);
            } elseif (is_object($client) && property_exists($client, $field)) {
                unset($client->$field);
            }
        }
        
        // Mark that this data has been filtered
        if (is_array($client)) {
            $client['is_filtered'] = true;
        } elseif (is_object($client)) {
            $client->is_filtered = true;
        }
    }
} 