package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.bean.EventoPatrocinioFacade;
import com.compreingressos.knowledge.controller.util.JsfUtil;
import com.compreingressos.knowledge.controller.util.JsfUtil.PersistAction;
import com.compreingressos.knowledge.model.EventoPatrocinio;
import com.compreingressos.knowledge.model.EventoPatrocinioPK;

import java.io.Serializable;
import java.util.List;
import java.util.ResourceBundle;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.EJB;
import javax.ejb.EJBException;
import javax.inject.Named;
import javax.enterprise.context.SessionScoped;
import javax.faces.component.UIComponent;
import javax.faces.context.FacesContext;
import javax.faces.convert.Converter;
import javax.faces.convert.FacesConverter;

@Named("eventoPatrocinioController")
@SessionScoped
public class EventoPatrocinioController implements Serializable {

    @EJB
    private com.compreingressos.knowledge.bean.EventoPatrocinioFacade ejbFacade;
    private List<EventoPatrocinio> items = null;
    private EventoPatrocinio selected;

    public EventoPatrocinioController() {
    }

    public EventoPatrocinio getSelected() {
        return selected;
    }

    public void setSelected(EventoPatrocinio selected) {
        this.selected = selected;
    }

    protected void setEmbeddableKeys() {
        selected.getEventoPatrocinioPK().setPatrocinadorId(selected.getPatrocinador().getId());
        selected.getEventoPatrocinioPK().setEventoId(selected.getEvento().getId());
    }

    protected void initializeEmbeddableKey() {
        selected.setEventoPatrocinioPK(new com.compreingressos.knowledge.model.EventoPatrocinioPK());
    }

    private EventoPatrocinioFacade getFacade() {
        return ejbFacade;
    }

    public EventoPatrocinio prepareCreate() {
        selected = new EventoPatrocinio();
        initializeEmbeddableKey();
        return selected;
    }

    public void create() {
        persist(PersistAction.CREATE, ResourceBundle.getBundle("/Bundle").getString("EventoPatrocinioCreated"));
        if (!JsfUtil.isValidationFailed()) {
            items = null;
        }
    }

    public void update() {
        persist(PersistAction.UPDATE, ResourceBundle.getBundle("/Bundle").getString("EventoPatrocinioUpdated"));
    }

    public void destroy() {
        persist(PersistAction.DELETE, ResourceBundle.getBundle("/Bundle").getString("EventoPatrocinioDeleted"));
        if (!JsfUtil.isValidationFailed()) {
            selected = null; // Remove selection
            items = null;    // Invalidate list of items to trigger re-query.
        }
    }

    public List<EventoPatrocinio> getItems() {
        if (items == null) {
            items = getFacade().findAll();
        }
        return items;
    }

    private void persist(PersistAction persistAction, String successMessage) {
        if (selected != null) {
            setEmbeddableKeys();
            try {
                if (persistAction != PersistAction.DELETE) {
                    getFacade().edit(selected);
                } else {
                    getFacade().remove(selected);
                }
                JsfUtil.addSuccessMessage(successMessage);
            } catch (EJBException ex) {
                String msg = "";
                Throwable cause = ex.getCause();
                if (cause != null) {
                    msg = cause.getLocalizedMessage();
                }
                if (msg.length() > 0) {
                    JsfUtil.addErrorMessage(msg);
                } else {
                    JsfUtil.addErrorMessage(ex, ResourceBundle.getBundle("/Bundle").getString("PersistenceErrorOccured"));
                }
            } catch (Exception ex) {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, null, ex);
                JsfUtil.addErrorMessage(ex, ResourceBundle.getBundle("/Bundle").getString("PersistenceErrorOccured"));
            }
        }
    }

    public EventoPatrocinio getEventoPatrocinio(EventoPatrocinioPK id) {
        return getFacade().find(id);
    }

    public List<EventoPatrocinio> getItemsAvailableSelectMany() {
        return getFacade().findAll();
    }

    public List<EventoPatrocinio> getItemsAvailableSelectOne() {
        return getFacade().findAll();
    }

    @FacesConverter(forClass = EventoPatrocinio.class)
    public static class EventoPatrocinioControllerConverter implements Converter {

        private static final String SEPARATOR = "#";
        private static final String SEPARATOR_ESCAPED = "\\#";

        @Override
        public Object getAsObject(FacesContext facesContext, UIComponent component, String value) {
            if (value == null || value.length() == 0) {
                return null;
            }
            EventoPatrocinioController controller = (EventoPatrocinioController) facesContext.getApplication().getELResolver().
                    getValue(facesContext.getELContext(), null, "eventoPatrocinioController");
            return controller.getEventoPatrocinio(getKey(value));
        }

        EventoPatrocinioPK getKey(String value) {
            com.compreingressos.knowledge.model.EventoPatrocinioPK key;
            String values[] = value.split(SEPARATOR_ESCAPED);
            key = new EventoPatrocinioPK();
            key.setPatrocinadorId(Integer.parseInt(values[0]));
            key.setEventoId(Integer.parseInt(values[1]));
            return key;
        }

        String getStringKey(EventoPatrocinioPK value) {
            StringBuilder sb = new StringBuilder();
            sb.append(value.getPatrocinadorId());
            sb.append(SEPARATOR);
            sb.append(value.getEventoId());
            return sb.toString();
        }

        @Override
        public String getAsString(FacesContext facesContext, UIComponent component, Object object) {
            if (object == null) {
                return null;
            }
            if (object instanceof EventoPatrocinio) {
                EventoPatrocinio o = (EventoPatrocinio) object;
                return getStringKey(o.getEventoPatrocinioPK());
            } else {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, "object {0} is of type {1}; expected type: {2}", new Object[]{object, object.getClass().getName(), EventoPatrocinio.class.getName()});
                return null;
            }
        }

    }

}
