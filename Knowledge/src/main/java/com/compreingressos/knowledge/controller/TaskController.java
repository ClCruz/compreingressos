package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.bpm.TaskBPM;
import com.compreingressos.knowledge.controller.util.JsfUtil;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import javax.annotation.PostConstruct;
import javax.faces.bean.ManagedBean;
import javax.faces.bean.ManagedProperty;
import javax.faces.bean.ViewScoped;
import org.apache.commons.codec.binary.Base64;
import org.apache.commons.codec.binary.StringUtils;
import org.kie.api.runtime.manager.audit.AuditService;
import org.kie.api.runtime.manager.audit.VariableInstanceLog;
import org.kie.api.task.TaskService;
import org.kie.api.task.model.Status;
import org.kie.api.task.model.Task;
import org.kie.api.task.model.TaskSummary;

/**
 *
 * @author edicarlos.barbosa
 */
@ManagedBean(name = "taskController")
@ViewScoped
public class TaskController implements Serializable{

    private Task task;
    private List<Task> listaTask;
    private String content;
    
    @ManagedProperty(name = "loginController", value = "#{loginController}")
    private LoginController loginController = new LoginController();
    
    public TaskController() {        
    }
    
    @PostConstruct
    public void initialize(){
        retrieveTasks();
    }

    public Task getTask() {
        return task;
    }

    public void setTask(Task task) {
        this.task = task;
    }

    public List<Task> getListaTask() {
        return listaTask;
    }

    public void setListaTask(List<Task> listaTask) {
        this.listaTask = listaTask;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }  

    public LoginController getLoginController() {
        return loginController;
    }

    public void setLoginController(LoginController loginController) {
        this.loginController = loginController;
    }
    
    public String processInstance(Task task, String variable){
        AuditService auditService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getAuditService();
        List<VariableInstanceLog> variablesLog = (List<VariableInstanceLog>) auditService.findVariableInstances(task.getTaskData().getProcessInstanceId(), variable);
        String value = null;
        for(VariableInstanceLog variableLog : variablesLog){
            value = variableLog.getValue();
            break;
        }
        return value;
    }
    
    public String redirect(Task task){
        TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
        if(task.getTaskData().getStatus() == Status.Reserved){
            taskService.start(task.getId(), loginController.getUsuario().getLogin());
        }        
        Map<String, Object> c = taskService.getTaskContent(task.getId());
        return "/pages/processo/"+c.get("TaskName").toString()+"?faces-redirect=true&amp;includeViewParams=true";
    }
    
    public void retrieveTasks() {   
        TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
        listaTask = new ArrayList();
        for(TaskSummary t : taskService.getTasksAssignedAsPotentialOwner(loginController.getUsuario().getLogin(), "en-UK")){            
            listaTask.add(taskService.getTaskById(t.getId()));            
        }
    }    

    public void startTask(Task task) {  
        TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
        taskService.start(task.getId(), loginController.getUsuario().getLogin());
        JsfUtil.addSuccessMessage("Tarefa " + task.getName() + " obtida.");
        retrieveTasks();
    }

    public void releaseTask(Task task) {
        TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
        taskService.release(task.getId(), loginController.getUsuario().getLogin());
        JsfUtil.addSuccessMessage("Tarefa " + task.getName() + " liberada.");
        retrieveTasks();
    }

    public void completeTask(Task task) {
        TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
        taskService.complete(task.getId(), loginController.getUsuario().getLogin(), new HashMap<String, Object>());
        JsfUtil.addSuccessMessage("Tarefa " + task.getName() + " concluída.");
        retrieveTasks();
    }
    
    public String decodeBase64(String c){
        return (c != null) ? StringUtils.newStringUtf8(Base64.decodeBase64(c)) : "";
    }
}
