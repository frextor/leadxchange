<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcePlanLimits;
use App\Services\ConnectionService;
use Illuminate\Http\Request;

/**
 * ConnectionController (WEB)
 * 
 * Thin controller - uses ConnectionService for business logic.
 */
class ConnectionController extends Controller
{
    use EnforcePlanLimits;

    protected ConnectionService $connectionService;

    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * Display all connections.
     * 
     * GET /connections
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status');

        switch ($type) {
            case 'received':
                $connections = $this->connectionService->getReceivedRequests(
                    $request->user(),
                    $status
                );
                break;

            case 'sent':
                $connections = $this->connectionService->getSentRequests(
                    $request->user(),
                    $status
                );
                break;

            case 'connections':
                $connections = $this->connectionService->getConnections(
                    $request->user()
                );
                break;

            default:
                $received = $this->connectionService->getReceivedRequests($request->user());
                $sent = $this->connectionService->getSentRequests($request->user());
                $accepted = $this->connectionService->getConnections($request->user());
                
                return view('connections.index', compact('received', 'sent', 'accepted'));
        }

        return view('connections.index', compact('connections', 'type'));
    }

    /**
     * Send a connection request.
     * 
     * POST /connections
     */
    public function store(Request $request)
    {
        if ($redirect = $this->requirePermission('can_send_invitations', 'Votre plan ne permet pas d\'envoyer des invitations de connexion.')) {
            return $redirect;
        }

        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $this->connectionService->sendRequest(
                $request->user(),
                $validated['receiver_id']
            );

            return back()->with('success', 'Connection request sent successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Accept a connection request.
     * 
     * POST /connections/{id}/accept
     */
    public function accept(int $id, Request $request)
    {
        try {
            $this->connectionService->acceptRequest($id, $request->user());

            return back()->with('success', 'Connection request accepted');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a connection request.
     * 
     * POST /connections/{id}/reject
     */
    public function reject(int $id, Request $request)
    {
        try {
            $this->connectionService->rejectRequest($id, $request->user());

            return back()->with('success', 'Connection request rejected');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a sent connection request.
     * 
     * DELETE /connections/{id}
     */
    public function destroy(int $id, Request $request)
    {
        try {
            $this->connectionService->cancelRequest($id, $request->user());

            return back()->with('success', 'Connection request cancelled');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
