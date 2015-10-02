/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import java.util.Date;
import javax.persistence.Basic;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.Lob;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "FATO_KBASE_EVENTO_CLIENTE")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "EventoCliente.findAll", query = "SELECT e FROM EventoCliente e"),
    @NamedQuery(name = "EventoCliente.findAllByTask", query = "SELECT e FROM EventoCliente e WHERE e.idTask = :idTask"),
    @NamedQuery(name = "EventoCliente.findAllByProcess", query = "SELECT e FROM EventoCliente e WHERE e.idProcess = :idProcess ORDER BY e.evento.descricaoCompleta"),
    @NamedQuery(name = "EventoCliente.findAllByProcessCliente", query = "SELECT e FROM EventoCliente e WHERE e.idProcess = :idProcess AND e.respostaCliente = true ORDER BY e.evento.descricaoCompleta"),
    @NamedQuery(name = "EventoCliente.findAllByProcessProdutor", query = "SELECT e FROM EventoCliente e WHERE e.idProcess = :idProcess AND e.respostaProdutor = true ORDER BY e.evento.descricaoCompleta"),
})
public class EventoCliente implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_KBEC")
    private Integer id;
    @Column(name = "DT_KBEC_ENVIO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataEnvio;
    @Column(name = "DT_KBEC_RESPOSTA")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataResposta;
    @Column(name = "DT_KBEC_CONFIRMACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataConfirmacao;
    @Column(name = "DT_KBEC_ACEITE_PRODUTOR")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataAceiteProdutor;
    @Column(name = "DT_KBEC_IMPLEMENTACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataImplementacao;    
    @Column(name = "DT_KBEC_CONTRATO_CLIENTE")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataEnvioContratoCliente;
    @Lob
    @Size(max = 2147483647)
    @Column(name = "DS_KBEC_OBS")
    private String observacao;
    @JoinColumn(name = "ID_KBMO", referencedColumnName = "ID_KBMO")
    @ManyToOne
    private Motivo motivo;
    @JoinColumn(name = "ID_KBEV", referencedColumnName = "ID_KBEV")
    @ManyToOne
    private Evento evento;
    @JoinColumn(name = "ID_KBCL", referencedColumnName = "ID_KBCL")
    @ManyToOne
    private Cliente cliente;
    @Column(name = "ID_TASK")
    private Long idTask;
    @Column(name = "ID_PROCESS")
    private Long idProcess;
    @Column(name = "IN_RESPOSTA_CLIENTE")
    private boolean respostaCliente;
    @Column(name = "IN_RESPOSTA_PRODUTOR")
    private boolean respostaProdutor;
    @Column(name = "DS_NOME_LISTA")
    @Size(max = 255)
    private String nomeLista;
    
    public EventoCliente() {
    }

    public EventoCliente(Integer id) {
        this.id = id;
    }
    
    public EventoCliente(Evento evento, Cliente cliente){
        this.evento = evento;
        this.cliente = cliente;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public Date getDataEnvio() {
        return dataEnvio;
    }

    public void setDataEnvio(Date dataEnvio) {
        this.dataEnvio = dataEnvio;
    }

    public Date getDataResposta() {
        return dataResposta;
    }

    public void setDataResposta(Date dataResposta) {
        this.dataResposta = dataResposta;
    }

    public Date getDataConfirmacao() {
        return dataConfirmacao;
    }

    public void setDataConfirmacao(Date dataConfirmacao) {
        this.dataConfirmacao = dataConfirmacao;
    }

    public Date getDataAceiteProdutor() {
        return dataAceiteProdutor;
    }

    public void setDataAceiteProdutor(Date dataAceiteProdutor) {
        this.dataAceiteProdutor = dataAceiteProdutor;
    }

    public Date getDataImplementacao() {
        return dataImplementacao;
    }

    public void setDataImplementacao(Date dataImplementacao) {
        this.dataImplementacao = dataImplementacao;
    }

    public Date getDataEnvioContratoCliente() {
        return dataEnvioContratoCliente;
    }

    public void setDataEnvioContratoCliente(Date dataEnvioContratoCliente) {
        this.dataEnvioContratoCliente = dataEnvioContratoCliente;
    }
    
    public String getObservacao() {
        return observacao;
    }

    public void setObservacao(String observacao) {
        this.observacao = observacao;
    }

    public Motivo getMotivo() {
        return motivo;
    }

    public void setMotivo(Motivo motivo) {
        this.motivo = motivo;
    }

    public Evento getEvento() {
        return evento;
    }

    public void setEvento(Evento evento) {
        this.evento = evento;
    }

    public Cliente getCliente() {
        return cliente;
    }

    public void setCliente(Cliente cliente) {
        this.cliente = cliente;
    }

    public boolean isRespostaCliente() {
        return respostaCliente;
    }

    public void setRespostaCliente(boolean respostaCliente) {
        this.respostaCliente = respostaCliente;
    }

    public boolean isRespostaProdutor() {
        return respostaProdutor;
    }

    public void setRespostaProdutor(boolean respostaProdutor) {
        this.respostaProdutor = respostaProdutor;
    }
        
    public Long getIdTask() {
        return idTask;
    }

    public void setIdTask(Long idTask) {
        this.idTask = idTask;
    }

    public Long getIdProcess() {
        return idProcess;
    }

    public void setIdProcess(Long idProcess) {
        this.idProcess = idProcess;
    }

    public String getNomeLista() {
        return nomeLista;
    }

    public void setNomeLista(String nomeLista) {
        this.nomeLista = nomeLista;
    }
                
    @Override
    public int hashCode() {
        int hash = 0;
        hash += (id != null ? id.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof EventoCliente)) {
            return false;
        }
        EventoCliente other = (EventoCliente) object;
        if ((this.id == null && other.id != null) || (this.id != null && !this.id.equals(other.id))) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return id.toString();
    }
    
}
