<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function index()
    {
        $tasks = auth()->user()->tasks;
        return response()->json(['tasks' => $tasks]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Pending,In Progress,Completed',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => auth()->id(),
        ]);
        
        // Log the task creation to Node.js service
        $this->logTaskActivity($task, 'created');
        
        return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
    }
    
    public function show($id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        
        return response()->json(['task' => $task]);
    }
    
    public function update(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:Pending,In Progress,Completed',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $task->update($request->all());
        
        // Log the task update to Node.js service
        $this->logTaskActivity($task, 'updated');
        
        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }
    
    public function destroy($id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        
        // Log the task deletion to Node.js service
        $this->logTaskActivity($task, 'deleted');
        
        $task->delete();
        
        return response()->json(['message' => 'Task deleted successfully']);
    }
    
    private function logTaskActivity($task, $action)
    {
        try {
            Http::post('http://localhost:3000/api/logs', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'task_data' => $task->toArray(),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            // Log error but don't interrupt the main process
            \Log::error('Failed to log task activity: ' . $e->getMessage());
        }
    }
}