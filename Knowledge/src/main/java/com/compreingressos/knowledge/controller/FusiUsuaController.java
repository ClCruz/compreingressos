package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.bean.FusiUsuaFacade;
import com.compreingressos.knowledge.controller.util.JsfUtil;
import com.compreingressos.knowledge.model.FuncaoSistema;
import com.compreingressos.knowledge.model.FusiUsua;
import com.compreingressos.knowledge.model.Usuario;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;
import java.util.ResourceBundle;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.EJB;
import javax.faces.application.FacesMessage;
import javax.faces.bean.ManagedBean;
import javax.faces.bean.ViewScoped;
import javax.faces.component.UIComponent;
import javax.faces.context.FacesContext;
import javax.faces.convert.Converter;
import javax.faces.convert.FacesConverter;
import javax.faces.event.ValueChangeEvent;
import org.primefaces.event.SelectEvent;
import org.primefaces.event.UnselectEvent;

@ManagedBean(name = "fusiUsuaController")
@ViewScoped
public class FusiUsuaController implements Serializable {

    @EJB
    private FusiUsuaFacade ejbFacade;
    private List<FuncaoSistema> items = null;
    private Usuario usuario;
    private List<FusiUsua> selected = null;
    private boolean todos;

    public FusiUsuaController() {
        prepareCreate();
    }

    public List<FusiUsua> getSelected() {
        return selected;
    }

    public void setSelected(List<FusiUsua> selected) {
        this.selected = selected;
    }

    public Usuario getUsuario() {
        return usuario;
    }

    public void setUsuario(Usuario usuario) {
        this.usuario = usuario;
    }

    public boolean isTodos() {
        return todos;
    }

    public void setTodos(boolean todos) {
        this.todos = todos;
    }

    private FusiUsuaFacade getFacade() {
        return ejbFacade;
    }

    public void prepareCreate() {
        selected = new ArrayList<>();
    }

    public void changeUsuario(ValueChangeEvent event) {
        prepareFuncoesSistema((Usuario) event.getNewValue());
    }

    public void prepareFuncoesSistema(Usuario usuario) {
        List<FusiUsua> funcoesSistemaTarget;
        List<FuncaoSistema> funcoes = new ArrayList<>();

        funcoesSistemaTarget = getFacade().findSelectedByUsuario(usuario);

        for (FuncaoSistema f : getFacade().findAllByUsuario()) {
            f.setFusiUsua(null);
            f.setSelected(false);
            for (FusiUsua fu : funcoesSistemaTarget) {
                if (fu.getFuncaoSistema().equals(f)) {
                    f.setFusiUsua(fu);
                    f.setSelected(true);
                }
            }
            funcoes.add(f);
        }

        todos = funcoesSistemaTarget.size() == funcoes.size();

        items = funcoes;
    }

    public void save() {
        if (usuario != null) {
            try {
                if (todos) {
                    getFacade().remove(usuario);
                    for (FuncaoSistema funcaoSistema : items) {
                        FusiUsua funcaoSistemaUsuario = new FusiUsua();
                        funcaoSistemaUsuario.setFuncaoSistema(funcaoSistema);
                        funcaoSistemaUsuario.setUsuario(usuario);
                        getFacade().edit(funcaoSistemaUsuario);
                    }
                    prepareFuncoesSistema(usuario);
                } else {
                    getFacade().remove(usuario);
                    prepareFuncoesSistema(usuario);
                }
                JsfUtil.addSuccessMessage("Permissão alterada com sucesso.");
            } catch (Exception ex) {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, ex.toString(), ex);
                JsfUtil.addErrorMessage(ex, ResourceBundle.getBundle("/Bundle").getString("PersistenceErrorOccured"));
            }
        } else {
            JsfUtil.addErrorMessage("O Usuário não foi selecionado.");
        }
    }

    public void change(FuncaoSistema funcaoSistema) {
        try {
            if (funcaoSistema.isSelected()) {
                getFacade().edit(new FusiUsua(funcaoSistema, usuario));
                todos = true;
                for (FuncaoSistema item : items) {
                    if (!item.isSelected()) {
                        todos = false;
                        break;
                    }                    
                }
            } else {
                getFacade().remove(funcaoSistema, usuario);
                todos = false;
            }
            JsfUtil.addSuccessMessage("Permissão alterada com sucesso.");
        } catch (Exception ex) {
            Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, ex.toString(), ex);
            JsfUtil.addErrorMessage(ex, ResourceBundle.getBundle("/Bundle").getString("PersistenceErrorOccured"));
        }
    }

    public List<FuncaoSistema> getItems() {
        return items;
    }

    public List<FusiUsua> getItemsAvailableSelectMany() {
        return getFacade().findAll();
    }

    public List<FusiUsua> getItemsAvailableSelectOne() {
        return getFacade().findAll();
    }

    public void onRowSelect(SelectEvent event) {
        FacesMessage msg = new FacesMessage("Car Selected", ((FusiUsua) event.getObject()).getId().toString());
        FacesContext.getCurrentInstance().addMessage(null, msg);
    }

    public void onRowUnselect(UnselectEvent event) {
        FacesMessage msg = new FacesMessage("Car Unselected", ((FusiUsua) event.getObject()).getId().toString());
        FacesContext.getCurrentInstance().addMessage(null, msg);
    }

    @FacesConverter(forClass = FusiUsua.class)
    public static class FusiUsuaControllerConverter implements Converter {

        @Override
        public Object getAsObject(FacesContext facesContext, UIComponent component, String value) {
            if (value == null || value.length() == 0) {
                return null;
            }
            FusiUsuaController controller = (FusiUsuaController) facesContext.getApplication().getELResolver().
                    getValue(facesContext.getELContext(), null, "fusiUsuaController");
            return controller.getFacade().find(getKey(value));
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
            if (object instanceof FusiUsua) {
                FusiUsua o = (FusiUsua) object;
                return getStringKey(o.getId());
            } else {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, "object {0} is of type {1}; expected type: {2}", new Object[]{object, object.getClass().getName(), FusiUsua.class.getName()});
                return null;
            }
        }

    }

}
