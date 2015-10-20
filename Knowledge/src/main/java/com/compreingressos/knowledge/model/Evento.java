/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import java.util.Date;
import java.util.List;
import javax.persistence.Basic;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.JoinTable;
import javax.persistence.Lob;
import javax.persistence.ManyToMany;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_KBASE_EVENTO")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "Evento.findAll", query = "SELECT e FROM Evento e ORDER BY e.descricaoCompleta"),
    @NamedQuery(name = "Evento.findAllByLocal", query = "SELECT e FROM Evento e WHERE e.local = :local ORDER BY e.descricaoCompleta")
})
public class Evento implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_KBEV")
    private Integer id;
    @Basic(optional = false)
    @NotNull
    @Size(min = 1, max = 30)
    @Column(name = "DS_KBEV_RESUMIDO")
    private String descricaoResumida;
    @Size(max = 255)
    @Column(name = "DS_KBEV_COMPLETO")
    private String descricaoCompleta;
    @Lob
    @Size(max = 2147483647)
    @Column(name = "DS_KBASE_EVENTO_SINOPSE")
    private String descricaoSinopse;
    @Column(name = "BK_KBEV")
    private Integer idExterno;
    @Basic(optional = false)
    @Column(name = "DT_ATUALIZACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dtAtualizacao;
    @JoinTable(name = "FATO_KBASE_EVENTO_PATROCINIO", joinColumns = {
        @JoinColumn(name = "ID_KBEV", referencedColumnName = "ID_KBEV")}, inverseJoinColumns = {
        @JoinColumn(name = "ID_KBPA", referencedColumnName = "ID_KBPA")})
    @ManyToMany
    private List<Patrocinador> listaPatrocinador;
    @OneToMany(mappedBy = "evento")
    private List<EventoCliente> listaEventoCliente;
    @JoinColumn(name = "ID_KBPR_PRODUTOR_NACIONAL", referencedColumnName = "ID_KBPR")
    @ManyToOne
    private Produtor produtorNacional;
    @JoinColumn(name = "ID_KBPR_PRODUTOR_LOCAL", referencedColumnName = "ID_KBPR")
    @ManyToOne
    private Produtor produtorLocal;
    @JoinColumn(name = "ID_KBLO", referencedColumnName = "ID_KBLO")
    @ManyToOne
    private Local local;
    @JoinColumn(name = "ID_KBGE", referencedColumnName = "ID_KBGE")
    @ManyToOne
    private Genero genero;
    @OneToMany(mappedBy = "evento")
    private List<Apresentacao> listaApresentacao;
    @Transient
    private boolean selected;

    public Evento() {
    }

    public Evento(Integer id) {
        this.id = id;
    }

    public Evento(Integer id, String dsResumida, Date dtAtualizacao) {
        this.id = id;
        this.descricaoResumida = dsResumida;
        this.dtAtualizacao = dtAtualizacao;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getDescricaoResumida() {
        return descricaoResumida;
    }

    public void setDescricaoResumida(String descricaoResumida) {
        this.descricaoResumida = descricaoResumida;
    }

    public String getDescricaoCompleta() {
        return descricaoCompleta;
    }

    public void setDescricaoCompleta(String descricaoCompleta) {
        this.descricaoCompleta = descricaoCompleta;
    }

    public String getDescricaoSinopse() {
        return descricaoSinopse;
    }

    public void setDescricaoSinopse(String descricaoSinopse) {
        this.descricaoSinopse = descricaoSinopse;
    }

    public Integer getIdExterno() {
        return idExterno;
    }

    public void setIdExterno(Integer idExterno) {
        this.idExterno = idExterno;
    }

    public Date getDtAtualizacao() {
        return dtAtualizacao;
    }

    public void setDtAtualizacao(Date dtAtualizacao) {
        this.dtAtualizacao = dtAtualizacao;
    }

    @XmlTransient
    public List<Patrocinador> getListaPatrocinador() {
        return listaPatrocinador;
    }

    public void setListaPatrocinador(List<Patrocinador> listaPatrocinador) {
        this.listaPatrocinador = listaPatrocinador;
    }

    @XmlTransient
    public List<EventoCliente> getListaEventoCliente() {
        return listaEventoCliente;
    }

    public void setListaEventoCliente(List<EventoCliente> listaEventoCliente) {
        this.listaEventoCliente = listaEventoCliente;
    }

    public Produtor getProdutorNacional() {
        return produtorNacional;
    }

    public void setProdutorNacional(Produtor produtorNacional) {
        this.produtorNacional = produtorNacional;
    }

    public Produtor getProdutorLocal() {
        return produtorLocal;
    }

    public void setProdutorLocal(Produtor produtorLocal) {
        this.produtorLocal = produtorLocal;
    }

    public Local getLocal() {
        return local;
    }

    public void setLocal(Local local) {
        this.local = local;
    }

    public Genero getGenero() {
        return genero;
    }

    public void setGenero(Genero genero) {
        this.genero = genero;
    }

    @XmlTransient
    public List<Apresentacao> getListaApresentacao() {
        return listaApresentacao;
    }

    public void setListaApresentacao(List<Apresentacao> listaApresentacao) {
        this.listaApresentacao = listaApresentacao;
    }

    @XmlTransient
    public boolean isSelected() {
        return selected;
    }

    public void setSelected(boolean selected) {
        this.selected = selected;
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
        if (!(object instanceof Evento)) {
            return false;
        }
        Evento other = (Evento) object;
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
