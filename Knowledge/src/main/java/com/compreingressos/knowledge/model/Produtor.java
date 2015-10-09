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
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_KBASE_PRODUTOR")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "Produtor.findAll", query = "SELECT p FROM Produtor p ORDER BY p.nomeProdutor")})
public class Produtor implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_KBPR")
    private Integer id;
    @Basic(optional = false)
    @NotNull
    @Size(min = 1, max = 60)
    @Column(name = "NM_KBPR_PRODUTOR")
    private String nomeProdutor;
    @Size(max = 60)
    @Column(name = "NM_KBPR_EMPRESA")
    private String nomeEmpresa;
    @Size(max = 14)
    @Column(name = "CD_KBPR_CNPJ")
    private String cnpj;
    @Size(max = 60)
    @Column(name = "DS_KBPR_ENDERECO")
    private String endereco;
    @Size(max = 8)
    @Column(name = "CD_KBPR_CEP")
    private String cep;
    @Size(max = 30)
    @Column(name = "CD_KBPR_BAIRRO")
    private String bairro;
    @Size(max = 15)
    @Column(name = "CD_KBPR_TELEFONE")
    private String telefone;
    @Size(max = 15)
    @Column(name = "CD_KBPR_CELULAR")
    private String celular;
    @Size(max = 100)
    @Column(name = "CD_KBPR_EMAIL1")
    private String email1;
    @Size(max = 100)
    @Column(name = "CD_KBPR_EMAIL2")
    private String email2;
    @Size(max = 100)
    @Column(name = "CD_KBPR_SITE")
    private String site;
    @Size(max = 100)
    @Column(name = "DS_KBPR_OBS")
    private String observacao;
    @Basic(optional = false)
    @NotNull
    @Column(name = "DT_ATUALIZACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dtAtualizacao;
    @OneToMany(mappedBy = "produtorNacional")
    private List<Evento> listaEventoProdutorNacional;
    @OneToMany(mappedBy = "produtorLocal")
    private List<Evento> listaEventoProdutorLocal;

    public Produtor() {
    }

    public Produtor(Integer id) {
        this.id = id;
    }

    public Produtor(Integer id, String nomeProdutor, Date dtAtualizacao) {
        this.id = id;
        this.nomeProdutor = nomeProdutor;
        this.dtAtualizacao = dtAtualizacao;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getNomeProdutor() {
        return nomeProdutor;
    }

    public void setNomeProdutor(String nomeProdutor) {
        this.nomeProdutor = nomeProdutor;
    }

    public String getNomeEmpresa() {
        return nomeEmpresa;
    }

    public void setNomeEmpresa(String nomeEmpresa) {
        this.nomeEmpresa = nomeEmpresa;
    }

    public String getCnpj() {
        return cnpj;
    }

    public void setCnpj(String cnpj) {
        this.cnpj = cnpj;
    }

    public String getEndereco() {
        return endereco;
    }

    public void setEndereco(String endereco) {
        this.endereco = endereco;
    }

    public String getCep() {
        return cep;
    }

    public void setCep(String cep) {
        this.cep = cep;
    }

    public String getBairro() {
        return bairro;
    }

    public void setBairro(String bairro) {
        this.bairro = bairro;
    }

    public String getTelefone() {
        return telefone;
    }

    public void setTelefone(String telefone) {
        this.telefone = telefone;
    }

    public String getCelular() {
        return celular;
    }

    public void setCelular(String celular) {
        this.celular = celular;
    }

    public String getEmail1() {
        return email1;
    }

    public void setEmail1(String email1) {
        this.email1 = email1;
    }

    public String getEmail2() {
        return email2;
    }

    public void setEmail2(String email2) {
        this.email2 = email2;
    }

    public String getSite() {
        return site;
    }

    public void setSite(String site) {
        this.site = site;
    }

    public String getObservacao() {
        return observacao;
    }

    public void setObservacao(String observacao) {
        this.observacao = observacao;
    }

    public Date getDtAtualizacao() {
        return dtAtualizacao;
    }

    public void setDtAtualizacao(Date dtAtualizacao) {
        this.dtAtualizacao = dtAtualizacao;
    }

    @XmlTransient
    public List<Evento> getListaEventoProdutorNacional() {
        return listaEventoProdutorNacional;
    }

    public void setListaEventoProdutorNacional(List<Evento> listaEventoProdutorNacional) {
        this.listaEventoProdutorNacional = listaEventoProdutorNacional;
    }

    @XmlTransient
    public List<Evento> getListaEventoProdutorLocal() {
        return listaEventoProdutorLocal;
    }

    public void setListaEventoProdutorLocal(List<Evento> listaEventoProdutorLocal) {
        this.listaEventoProdutorLocal = listaEventoProdutorLocal;
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
        if (!(object instanceof Produtor)) {
            return false;
        }
        Produtor other = (Produtor) object;
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
