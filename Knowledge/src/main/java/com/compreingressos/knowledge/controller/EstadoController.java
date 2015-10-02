/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.bean.EstadoFacade;
import com.compreingressos.knowledge.model.Estado;
import javax.inject.Named;
import javax.enterprise.context.SessionScoped;
import java.io.Serializable;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.EJB;
import javax.faces.component.UIComponent;
import javax.faces.context.FacesContext;
import javax.faces.convert.Converter;
import javax.faces.convert.FacesConverter;

/**
 *
 * @author edicarlos.barbosa
 */
@Named(value = "estadoController")
@SessionScoped
public class EstadoController implements Serializable {
    
    @EJB
    private com.compreingressos.knowledge.bean.EstadoFacade ejbFacade;
    private List<Estado> items = null;
    private Estado selected;
    /**
     * Creates a new instance of EstadoController
     */
    public EstadoController() {
    }

    public Estado getSelected() {
        return selected;
    }

    public void setSelected(Estado selected) {
        this.selected = selected;
    }
    
    protected void setEmbeddableKeys() {
    }

    protected void initializeEmbeddableKey() {
    }
    
    private EstadoFacade getFacade() {
        return ejbFacade;
    }
    
    public List<Estado> getItems() {
        if (items == null) {
            items = getFacade().findAll();
        }
        return items;
    }
    
    public Estado getEstado(java.lang.Integer id) {
        return getFacade().find(id);
    }

    public List<Estado> getItemsAvailableSelectMany() {
        return getFacade().findAll();
    }

    public List<Estado> getItemsAvailableSelectOne() {
        return getFacade().findAll();
    }
    
    @FacesConverter(forClass = Estado.class, value = "estadoControllerConverter")
    public static class EstadoControllerConverter implements Converter {

        @Override
        public Object getAsObject(FacesContext facesContext, UIComponent component, String value) {
            if (value == null || value.length() == 0) {
                return null;
            }
            EstadoController controller = (EstadoController) facesContext.getApplication().getELResolver().
                    getValue(facesContext.getELContext(), null, "estadoController");
            return controller.getEstado(getKey(value));
        }

        java.lang.Integer getKey(String value) {
            java.lang.Integer key;
            key = Integer.valueOf(value);
            return key;
        }

        String getStringKey(java.lang.Integer value) {
            StringBuilder sb = new StringBuilder();
            sb.append(value);
            return sb.toString();
        }

        @Override
        public String getAsString(FacesContext facesContext, UIComponent component, Object object) {
            if (object == null) {
                return null;
            }
            if (object instanceof Estado) {
                Estado o = (Estado) object;
                return getStringKey(o.getId());
            } else {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, "object {0} is of type {1}; expected type: {2}", new Object[]{object, object.getClass().getName(), Estado.class.getName()});
                return null;
            }
        }

    }
}
