package com.compreingressos.knowledge.bpm;

import com.compreingressos.knowledge.model.Usuario;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.List;
import org.kie.api.runtime.manager.RuntimeEngine;
import org.kie.api.runtime.manager.audit.AuditService;
import org.kie.api.runtime.manager.audit.VariableInstanceLog;
import org.kie.api.task.model.Task;
import org.kie.services.client.api.RemoteRuntimeEngineFactory;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 *
 * @author edicarlosbarbosa
 */
public final class TaskBPM {

    private static final String URL = "http://127.0.0.1:8080/jbpm-console";
    private static final Logger logger = LoggerFactory.getLogger(TaskBPM.class);

    private static final String deploymentId = "com.compreingressos:knowledge:1.0.0";
    private static RuntimeEngine engineFactory = null;    
    
    public static RuntimeEngine getRuntimeEngine(Usuario usuario) {
        if (engineFactory == null) {
            try {
                URL jbpmURL = new URL(URL);
                engineFactory = RemoteRuntimeEngineFactory.newRestBuilder()
                        .addUrl(jbpmURL)
                        .addUserName(usuario.getLogin())
                        .addPassword(usuario.getPassword())
                        .addDeploymentId(deploymentId)
                        .build();
            } catch (MalformedURLException ex) {
                logger.error("Error when build URL for jBPM: " + URL, ex);
            }
        }
        return engineFactory;
    }
    
    public static String processInstance(Task task, String variable, Usuario usuario){
        AuditService auditService = TaskBPM.getRuntimeEngine(usuario).getAuditService();
        List<VariableInstanceLog> variablesLog = (List<VariableInstanceLog>) auditService.findVariableInstances(task.getTaskData().getProcessInstanceId(), variable);
        String value = null;
        for(VariableInstanceLog variableLog : variablesLog){
            value = variableLog.getValue();
            break;
        }
        return value;
    }
    
}
