import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify';
import axios from 'axios';

const Dashboard = () => {
  const [tasks, setTasks] = useState([]);
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    status: 'Pending'
  });
  const [editMode, setEditMode] = useState(false);
  const [currentTaskId, setCurrentTaskId] = useState(null);
  
  const token = localStorage.getItem('token');
  
  const fetchTasks = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/tasks', {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTasks(response.data.tasks);
    } catch (error) {
      toast.error('Failed to fetch tasks.');
    }
  };
  
  useEffect(() => {
    fetchTasks();
  }, []);
  
  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editMode) {
        await axios.put(`http://localhost:8000/api/tasks/${currentTaskId}`, formData, {
          headers: { Authorization: `Bearer ${token}` }
        });
        toast.success('Task updated successfully!');
        setEditMode(false);
        setCurrentTaskId(null);
      } else {
        await axios.post('http://localhost:8000/api/tasks', formData, {
          headers: { Authorization: `Bearer ${token}` }
        });
        toast.success('Task created successfully!');
      }
      setFormData({ title: '', description: '', status: 'Pending' });
      fetchTasks();
    } catch (error) {
      toast.error('Operation failed. Please try again.');
    }
  };
  
  const handleEdit = (task) => {
    setFormData({
      title: task.title,
      description: task.description || '',
      status: task.status
    });
    setEditMode(true);
    setCurrentTaskId(task.id);
  };
  
  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this task?')) {
      try {
        await axios.delete(`http://localhost:8000/api/tasks/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        toast.success('Task deleted successfully!');
        fetchTasks();
      } catch (error) {
        toast.error('Failed to delete task.');
      }
    }
  };
  
  return (
    <div className="row">
      <div className="col-md-4">
        <div className="card">
          <div className="card-header">
            {editMode ? 'Edit Task' : 'Create New Task'}
          </div>
          <div className="card-body">
            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label className="form-label">Title</label>
                <input
                  type="text"
                  className="form-control"
                  name="title"
                  value={formData.title}
                  onChange={handleChange}
                  required
                />
              </div>
              <div className="mb-3">
                <label className="form-label">Description</label>
                <textarea
                  className="form-control"
                  name="description"
                  value={formData.description}
                  onChange={handleChange}
                  rows="3"
                ></textarea>
              </div>
              <div className="mb-3">
                <label className="form-label">Status</label>
                <select
                  className="form-select"
                  name="status"
                  value={formData.status}
                  onChange={handleChange}
                >
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
              <div className="d-flex justify-content-between">
                <button type="submit" className="btn btn-primary">
                  {editMode ? 'Update Task' : 'Create Task'}
                </button>
                {editMode && (
                  <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={() => {
                      setEditMode(false);
                      setCurrentTaskId(null);
                      setFormData({ title: '', description: '', status: 'Pending' });
                    }}
                  >
                    Cancel
                  </button>
                )}
              </div>
            </form>
          </div>
        </div>
      </div>
      <div className="col-md-8">
        <div className="card">
          <div className="card-header">My Tasks</div>
          <div className="card-body">
            {tasks.length === 0 ? (
              <p className="text-center">No tasks found. Create one now!</p>
            ) : (
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead>
                    <tr>
                      <th>Title</th>
                      <th>Description</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {tasks.map((task) => (
                      <tr key={task.id}>
                        <td>{task.title}</td>
                        <td>{task.description || '-'}</td>
                        <td>
                          <span className={`badge ${
                            task.status === 'Completed' ? 'bg-success' :
                            task.status === 'In Progress' ? 'bg-warning' : 'bg-secondary'
                          }`}>
                            {task.status}
                          </span>
                        </td>
                        <td>
                          <button
                            className="btn btn-sm btn-primary me-2"
                            onClick={() => handleEdit(task)}
                          >
                            Edit
                          </button>
                          <button
                            className="btn btn-sm btn-danger"
                            onClick={() => handleDelete(task.id)}
                          >
                            Delete
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;