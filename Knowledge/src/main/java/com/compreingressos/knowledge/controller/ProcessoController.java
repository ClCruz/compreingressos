package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.bean.EventoClienteFacade;
import com.compreingressos.knowledge.bean.EventoFacade;
import com.compreingressos.knowledge.bpm.TaskBPM;
import com.compreingressos.knowledge.controller.util.JsfUtil;
import com.compreingressos.knowledge.exception.KnowledgeException;
import com.compreingressos.knowledge.model.Cliente;
import com.compreingressos.knowledge.model.Evento;
import com.compreingressos.knowledge.model.EventoCliente;

import java.io.Serializable;
import java.text.Normalizer;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.EJB;
import javax.ejb.EJBException;
import javax.faces.bean.ManagedBean;
import javax.faces.bean.ManagedProperty;
import javax.faces.bean.ViewScoped;
import org.apache.commons.codec.binary.Base64;
import org.apache.commons.codec.binary.StringUtils;
import org.kie.api.runtime.manager.audit.AuditService;
import org.kie.api.runtime.manager.audit.VariableInstanceLog;
import org.kie.api.task.TaskService;
import org.kie.api.task.model.Task;
import org.primefaces.event.RowEditEvent;

@ManagedBean(name = "processoController")
@ViewScoped
public class ProcessoController implements Serializable {

    @EJB
    private EventoClienteFacade ejbFacade;
    @EJB
    private EventoFacade ejbEventoFacade;    

    private List<EventoCliente> items = null;
    private List<Evento> eventos = null;

    private Task task;
    private Date dataEnvio;
    private Cliente cliente;
    private String nomeLista;

    private Boolean respostaCliente;
    private Boolean respostaProdutor;

    private final SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy");

    @ManagedProperty(name = "loginController", value = "#{loginController}")
    private LoginController loginController = new LoginController();

    

    public ProcessoController() {

    }

    public void obtemProcesso() {
        if (task != null) {
            try {
                dataEnvio = sdf.parse(TaskBPM.processInstance(task, "dataEnvio", loginController.getUsuario()));
                cliente = new Cliente(Integer.parseInt(TaskBPM.processInstance(task, "cliente", loginController.getUsuario())));
                nomeLista = StringUtils.newStringUtf8(Base64.decodeBase64(TaskBPM.processInstance(task, "nomeLista", loginController.getUsuario())));
            } catch (EJBException ex) {
                Logger.getLogger(this.getClass().getName()).log(Level.INFO, "EJB do Processo indisponível", ex);
            } catch (NullPointerException ex) {
                Logger.getLogger(this.getClass().getName()).log(Level.INFO, "Nenhuma informa\u00e7\u00e3o encontrada para a Tarefa: {0}", task.getName());
            } catch (Exception ex) {
                Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, ex.getMessage(), ex);
            }
        }
    }

    public List<EventoCliente> getItems() {
        if (items == null) {
            items = getFacade().findAllByProcess(task.getTaskData().getProcessInstanceId());
        }
        return items;
    }

    public List<EventoCliente> getItemsCliente() {
        if (items == null) {
            items = getFacade().findAllByProcessCliente(task.getTaskData().getProcessInstanceId());
        }
        return items;
    }

    public List<EventoCliente> getItemsProdutor() {
        if (items == null) {
            items = getFacade().findAllByProcessProdutor(task.getTaskData().getProcessInstanceId());
        }
        return items;
    }

    public void setItems(List<EventoCliente> items) {
        this.items = items;
    }

    private EventoClienteFacade getFacade() {
        return ejbFacade;
    }

    public Task getTask() {
        return task;
    }

    public void setTask(Task task) {
        this.task = task;
    }

    public Date getDataEnvio() {
        return dataEnvio;
    }

    public void setDataEnvio(Date dataEnvio) {
        this.dataEnvio = dataEnvio;
    }

    public Cliente getCliente() {
        return cliente;
    }

    public void setCliente(Cliente cliente) {
        this.cliente = cliente;
    }

    public List<Evento> getEventos() {
        if (eventos == null) {
            eventos = ejbEventoFacade.findAllByApresentacao();
        }
        return eventos;
    }

    public void setEventos(List<Evento> eventos) {
        this.eventos = eventos;
    }

    public String getNomeLista() {
        return nomeLista;
    }

    public void setNomeLista(String nomeLista) {
        this.nomeLista = nomeLista;
    }        

    public void setRespostaCliente(Boolean respostaCliente) {
        this.respostaCliente = respostaCliente;
    }

    public Boolean getRespostaCliente() {
        return respostaCliente;
    }

    public Boolean getRespostaProdutor() {
        return respostaProdutor;
    }

    public void setRespostaProdutor(Boolean respostaProdutor) {
        this.respostaProdutor = respostaProdutor;
    }

    public LoginController getLoginController() {
        return loginController;
    }

    public void setLoginController(LoginController loginController) {
        this.loginController = loginController;
    }    

    public Long getProcessoId(Task task) {
        AuditService auditService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getAuditService();
        List<VariableInstanceLog> log = (List<VariableInstanceLog>) auditService.findVariableInstances(task.getTaskData().getProcessInstanceId(), "ProcessoID");
        String value = null;
        for (VariableInstanceLog log2 : log) {
            value = log2.getValue();
            break;
        }
        return (value == null) ? 0 : Long.valueOf(value);
    }

    public void releaseTask() {
        TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
        taskService.release(task.getId(), loginController.usuario.getLogin());
        JsfUtil.addSuccessMessage("Tarefa " + task.getName() + " liberada.");
    }

    public void completeTask(int passo) {
        try {
            if (passo == 1) {
                boolean selected = false;
                for (Evento e : eventos) {
                    if (e.isSelected()) {
                        selected = true;
                    }
                }

                if (selected) {
                    criarProcesso();
                } else {
                    throw new KnowledgeException("É necessário selecionar um evento.");
                }
            } else {
                salvarProcesso();
            }
            TaskService taskService = TaskBPM.getRuntimeEngine(loginController.getUsuario()).getTaskService();
            taskService.complete(task.getId(), loginController.usuario.getLogin(), getVars(passo));
            JsfUtil.addSuccessMessage("Tarefa " + task.getName() + " concluída.");
        } catch (KnowledgeException ex) {
            JsfUtil.addErrorMessage(ex.getMessage());
        } catch (Exception ex) {
            Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, null, ex);
            JsfUtil.addErrorMessage(ex, "Não foi possível concluir a tarefa.");
        }
    }

    public Map<String, Object> getVars(int passo) {
        Map<String, Object> vars = new HashMap<>();
        switch (passo) {
            case 1:
                vars.put("dataEnvio_", sdf.format(dataEnvio));
                vars.put("cliente_", cliente.getId());
                vars.put("nomeLista_", Base64.encodeBase64String(StringUtils.getBytesUtf8(nomeLista)));
                break;
            case 2:
                vars.put("respostaCliente_", true);
                break;
            case 3:
                vars.put("respostaProdutor_", true);
                break;
        }
        return vars;
    }

    public String toSeparateString(List<Evento> pEventos) {
        String lista = "";
        for (Evento evento : pEventos) {
            lista += evento.getId().toString() + ",";
        }
        return lista.substring(0, lista.length() - 1);
    }

    public String removerAcentos(String str) {
        return Normalizer.normalize(str, Normalizer.Form.NFD).replaceAll("[^\\p{ASCII}]", "");
    }

    public void criarProcesso() {
        try {
            for (Evento e : eventos) {
                if (e.isSelected()) {
                    EventoCliente ec = new EventoCliente(e, cliente);
                    ec.setDataEnvio(dataEnvio);
                    ec.setIdTask(task.getId());
                    ec.setIdProcess(task.getTaskData().getProcessInstanceId());
                    ec.setNomeLista(nomeLista);
                    getFacade().edit(ec);
                }
            }
        } catch (Exception ex) {
            Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, ex.getMessage(), ex);
            JsfUtil.addErrorMessage(ex, "Não foi salvar os dados da tarefa.");
        }
    }

    public void salvarProcesso() {
        try {
            for (EventoCliente ec : items) {
                getFacade().edit(ec);
            }
        } catch (Exception ex) {
            Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, ex.getMessage(), ex);
            JsfUtil.addErrorMessage(ex, "Não foi salvar os dados da tarefa.");
        }
    }
    
    public void onRowEdit(RowEditEvent event) {
        try {
            EventoCliente ec = (EventoCliente) event.getObject();
            getFacade().edit(ec);
        } catch (Exception ex) {
            Logger.getLogger(this.getClass().getName()).log(Level.SEVERE, ex.getMessage(), ex);
            JsfUtil.addErrorMessage(ex, "Não foi salvar os dados da tarefa.");
        }
    }
}
