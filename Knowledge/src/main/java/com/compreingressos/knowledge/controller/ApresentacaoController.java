package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.model.Apresentacao;
import com.compreingressos.knowledge.controller.util.JsfUtil;
import com.compreingressos.knowledge.controller.util.JsfUtil.PersistAction;
import com.compreingressos.knowledge.bean.ApresentacaoFacade;
import com.compreingressos.knowledge.model.ApresentacaoDetalhe;
import com.compreingressos.knowledge.model.Dia;
import com.compreingressos.knowledge.model.Mes;

import java.io.Serializable;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
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
import org.joda.time.DateTime;
import org.joda.time.Days;

@Named("apresentacaoController")
@SessionScoped
public class ApresentacaoController implements Serializable {

    @EJB
    private com.compreingressos.knowledge.bean.ApresentacaoFacade ejbFacade;
    private List<Apresentacao> items = null;
    private Apresentacao selected;
    private List<ApresentacaoDetalhe> detalhes = null;

    public ApresentacaoController() {
    }

    public Apresentacao getSelected() {
        return selected;
    }

    public void setSelected(Apresentacao selected) {
        this.selected = selected;
    }

    public List<ApresentacaoDetalhe> getDetalhes() {
        return detalhes;
    }

    public void setDetalhes(List<ApresentacaoDetalhe> detalhes) {
        this.detalhes = detalhes;
    }

    protected void setEmbeddableKeys() {
    }

    protected void initializeEmbeddableKey() {
    }

    private ApresentacaoFacade getFacade() {
        return ejbFacade;
    }

    public Apresentacao prepareCreate() {
        selected = new Apresentacao();
        createDetalhes();
        initializeEmbeddableKey();
        return selected;
    }

    public void create() {
        persist(PersistAction.CREATE, ResourceBundle.getBundle("/Bundle").getString("ApresentacaoCreated"));
        if (!JsfUtil.isValidationFailed()) {
            items = null;    // Invalidate list of items to trigger re-query.
        }
    }

    public void update() {
        persist(PersistAction.UPDATE, ResourceBundle.getBundle("/Bundle").getString("ApresentacaoUpdated"));
    }

    public void destroy() {
        persist(PersistAction.DELETE, ResourceBundle.getBundle("/Bundle").getString("ApresentacaoDeleted"));
        if (!JsfUtil.isValidationFailed()) {
            selected = null; // Remove selection
            items = null;    // Invalidate list of items to trigger re-query.
        }
    }

    public List<Apresentacao> getItems() {
        if (items == null) {
            items = getFacade().findAll();
        }
        return items;
    }

    private void persist(PersistAction persistAction, String successMessage) {
        if (selected != null) {
            setEmbeddableKeys();
            try {
                SimpleDateFormat sdf = new SimpleDateFormat("yyyyMMdd");
                SimpleDateFormat sdfMes = new SimpleDateFormat("yyyyMM");
                if (persistAction == PersistAction.CREATE) {
                    int diffDays = Days.daysBetween(new DateTime(selected.getData()), new DateTime(selected.getDataFinal())).getDays();
                    Date data = selected.getData();
                    for(int i = 0; i <= diffDays; i++) {
                        Calendar calendar = new GregorianCalendar(data.getYear(), data.getMonth(), data.getDay());
                        int diaSemana = calendar.get(Calendar.DAY_OF_WEEK) - 1;
                        if(detalhes.get(diaSemana).isSelected()){                            
                            selected.setDia(new Dia(Integer.parseInt(sdf.format(selected.getData()))));
                            selected.setMes(new Mes(Integer.parseInt(sdfMes.format(selected.getData()))));
                            selected.setDataAtualizacao(new Date(System.currentTimeMillis()));                            
                            selected.setHora(detalhes.get(diaSemana).getHora());
                            selected.setValorIngresso(detalhes.get(diaSemana).getValor());
                            getFacade().edit(selected);
                        }
                        Calendar c = Calendar.getInstance();
                        c.setTime(data);
                        c.add(Calendar.DATE, 1);
                        data = c.getTime();
                    }
                } else if(persistAction == PersistAction.UPDATE) {
                    selected.setDia(new Dia(Integer.parseInt(sdf.format(selected.getData()))));
                    selected.setMes(new Mes(Integer.parseInt(sdfMes.format(selected.getData()))));
                    selected.setDataAtualizacao(new Date(System.currentTimeMillis()));
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

    public Apresentacao getApresentacao(java.lang.Integer id) {
        return getFacade().find(id);
    }

    public List<Apresentacao> getItemsAvailableSelectMany() {
        return getFacade().findAll();
    }

    public List<Apresentacao> getItemsAvailableSelectOne() {
        return getFacade().findAll();
    }

    public void createDetalhes() {
        detalhes = new ArrayList<>();
        for (int i = 0; i < 7; i++) {
            detalhes.add(i, new ApresentacaoDetalhe(false));
        }
    }

    public void validarDias() {
        if(selected.getData() != null && selected.getDataFinal() != null){
            int diffDays = Days.daysBetween(new DateTime(selected.getData()), new DateTime(selected.getDataFinal())).getDays();
            System.out.println("Diff: " + diffDays);
            Date data = selected.getData();

            for (int i = 0; i < 7; i++) {
                detalhes.set(i, new ApresentacaoDetalhe(false));
            }

            if (diffDays <= 6) {
                while (data.getTime() <= selected.getDataFinal().getTime()) {
                    System.out.println("Data: " + data);
                    Calendar calendar = new GregorianCalendar(data.getYear(), data.getMonth(), data.getDay());
                    int diaSemana = calendar.get(Calendar.DAY_OF_WEEK) - 1;
                    System.out.println("Dia Semana: " + diaSemana);
                    detalhes.set(diaSemana, new ApresentacaoDetalhe(true));
                    Calendar c = Calendar.getInstance();
                    c.setTime(data);
                    c.add(Calendar.DATE, 1);
                    data = c.getTime();
                }
            } else {
                for (int i = 0; i < 7; i++) {
                    detalhes.set(i, new ApresentacaoDetalhe(true));
                }
            }
        }
    }

    @FacesConverter(forClass = Apresentacao.class)
    public static class ApresentacaoControllerConverter implements Converter {

        @Override
        public Object getAsObject(FacesContext facesContext, UIComponent component, String value) {
            if (value == null || value.length() == 0) {
                return null;
            }
            ApresentacaoController controller = (ApresentacaoController) facesContext.getApplication().getELResolver().
                    getValue(facesContext.getELContext(), null, "apresentacaoController");
            return controller.getApresentacao(getKey(value));
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
            if (object instanceof Apresentacao) {
                Apresentacao o = (Apresentacao) object;
                return getStringKey(o.getId());
            } else {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, "object {0} is of type {1}; expected type: {2}", new Object[]{object, object.getClass().getName(), Apresentacao.class.getName()});
                return null;
            }
        }

    }

}
