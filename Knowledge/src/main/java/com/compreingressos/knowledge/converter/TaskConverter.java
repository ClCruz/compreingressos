package com.compreingressos.knowledge.converter;

import com.compreingressos.knowledge.bpm.TaskBPM;
import com.compreingressos.knowledge.controller.LoginController;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.faces.bean.ManagedProperty;
import javax.faces.component.UIComponent;
import javax.faces.context.FacesContext;
import javax.faces.convert.Converter;
import javax.faces.convert.FacesConverter;
import org.kie.api.task.model.Task;

/**
 *
 * @author edicarlosbarbosa
 */
@FacesConverter("taskConverter")
public class TaskConverter implements Converter{
    
    @ManagedProperty(name = "loginController", value = "#{loginController}")
    private LoginController loginController = new LoginController();
    
    @Override
    public Object getAsObject(FacesContext context, UIComponent component, String value) {
        if (value == null || value.length() == 0) {
            return null;
        }        
        return TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService().getTaskById(Long.valueOf(value));
    }

    @Override
    public String getAsString(FacesContext context, UIComponent component, Object value) {
        if (value == null) {
            return null;
        }
        if (value instanceof Task) {
            Task o = (Task) value;
            return o.getId().toString();
        } else {
            Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, "object {0} is of type {1}; expected type: {2}", new Object[]{value, value.getClass().getName(), Task.class.getName()});
            return null;
        }
    }
    
    
}
